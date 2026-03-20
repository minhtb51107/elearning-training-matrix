<script setup>
import { watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    courses: Array,
    selectedCourse: Object,
    departments: Array,
    instructors: Array
});

// Khởi tạo Form và tự động fill giá trị mặc định nếu đi từ Chi tiết Khóa học sang
const form = useForm({
    course_id: props.selectedCourse?.id || '',
    name: '',
    instructor_id: '',
    duration: props.selectedCourse?.duration || '', // View only
    start_date: '',
    end_date: '',
    department_id: '',
    max_students: '',
    format: props.selectedCourse?.format || 'Offline',
    roles: 'Team lead', // Giao diện cứng tạm thời
    action: 'published'
});

// Tự động cập nhật Thời lượng và Hình thức khi đổi Khóa học ở Select
watch(() => form.course_id, (newCourseId) => {
    const course = props.courses.find(c => c.id === newCourseId);
    if (course) {
        form.duration = course.duration;
        form.format = course.format || 'Offline';
    } else {
        form.duration = '';
        form.format = 'Offline';
    }
});

const submitForm = (actionType) => {
    form.action = actionType;
    form.post(route('system.classes.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Tạo lớp học" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('system.courses.index')" class="font-bold text-xl text-blue-600 hover:underline">
                &lt; Quay lại danh sách
            </Link>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-8 border-b pb-4">Tạo lớp học:</h2>

                    <form @submit.prevent>
                        
                        <div class="flex flex-col items-center justify-center gap-2 mb-10 border-b border-gray-200 pb-8">
                            <div class="flex items-center justify-center gap-4">
                                <label class="text-base font-bold text-gray-800">Chọn khóa học:</label>
                                <select v-model="form.course_id" class="border-gray-400 rounded-sm text-[15px] w-[400px] focus:ring-gray-500 py-2">
                                    <option value="" disabled>-- Chọn khóa học cần mở lớp --</option>
                                    <option v-for="course in courses" :key="course.id" :value="course.id">
                                        {{ course.code }} - {{ course.name }}
                                    </option>
                                </select>
                            </div>
                            <div v-if="form.errors.course_id" class="text-red-500 text-xs font-bold">{{ form.errors.course_id }}</div>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">1. Thông tin lớp học:</h3>
                            <div class="grid grid-cols-2 gap-x-12 gap-y-6 ml-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Mã lớp học:</label>
                                    <div class="flex items-center gap-2">
                                        <input type="text" disabled class="w-full bg-gray-50 border-gray-300 rounded-sm text-gray-500 text-sm cursor-not-allowed">
                                        <span class="text-xs text-gray-500">(auto)</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Giảng viên:</label>
                                    <select v-model="form.instructor_id" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                        <option value="">-- Chưa phân công --</option>
                                        <option v-for="inst in instructors" :key="inst.id" :value="inst.id">{{ inst.name }}</option>
                                    </select>
                                    <div v-if="form.errors.instructor_id" class="text-red-500 text-xs mt-1">{{ form.errors.instructor_id }}</div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Tên lớp học: <span class="text-red-500">*</span></label>
                                    <input v-model="form.name" type="text" placeholder="Ví dụ: K01, Đợt 1..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 placeholder-gray-400">
                                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Thời lượng (Giờ):</label>
                                    <input v-model="form.duration" type="text" disabled class="w-full bg-gray-50 border-gray-300 rounded-sm text-gray-500 text-sm cursor-not-allowed">
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Thời gian học:</label>
                                    <div class="flex gap-8">
                                        <div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-sm font-bold text-gray-700 w-16">Từ ngày <span class="text-red-500">*</span>:</span>
                                                <input v-model="form.start_date" type="date" class="border-gray-400 rounded-sm text-sm focus:ring-gray-500 w-48 text-gray-700">
                                            </div>
                                            <div v-if="form.errors.start_date" class="text-red-500 text-xs mt-1 ml-20">{{ form.errors.start_date }}</div>
                                        </div>
                                        
                                        <div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-sm font-bold text-gray-700">Đến ngày <span class="text-red-500">*</span>:</span>
                                                <input v-model="form.end_date" type="date" class="border-gray-400 rounded-sm text-sm focus:ring-gray-500 w-48 text-gray-700">
                                            </div>
                                            <div v-if="form.errors.end_date" class="text-red-500 text-xs mt-1 ml-20">{{ form.errors.end_date }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-12">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">2. Phạm vi & Đối tượng:</h3>
                            <div class="grid grid-cols-2 gap-x-12 gap-y-6 ml-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Phòng ban (Áp dụng):</label>
                                    <select v-model="form.department_id" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                        <option value="">-- Toàn công ty --</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                    </select>
                                    <div v-if="form.errors.department_id" class="text-red-500 text-xs mt-1">{{ form.errors.department_id }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Số lượng tối đa: <span class="text-red-500">*</span></label>
                                    <input v-model="form.max_students" type="number" min="1" placeholder="VD: 30" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 placeholder-gray-400">
                                    <div v-if="form.errors.max_students" class="text-red-500 text-xs mt-1">{{ form.errors.max_students }}</div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Vai trò công việc:</label>
                                    <div class="relative">
                                        <select v-model="form.roles" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 appearance-none">
                                            <option>Team lead</option>
                                            <option>Nhân viên</option>
                                            <option>Trưởng phòng</option>
                                        </select>
                                        <span class="absolute right-8 top-1/2 -translate-y-1/2 text-xs text-gray-500">(CHỌN NHIỀU)</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Hình thức (Ghi đè khóa học nếu cần):</label>
                                    <select v-model="form.format" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                        <option value="Offline">Offline</option>
                                        <option value="Online">Online</option>
                                        <option value="Hybrid">Hybrid</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center gap-6 mt-8 pt-8 border-t border-gray-200">
                            <Link :href="route('system.courses.index')" class="bg-[#c93b42] hover:bg-red-800 text-white font-bold py-2.5 px-12 rounded-sm shadow-sm transition">
                                Hủy
                            </Link>
                            <button @click="submitForm('draft')" :disabled="form.processing" type="button" class="bg-[#e5e7eb] hover:bg-gray-300 text-gray-900 border border-gray-400 font-bold py-2.5 px-8 rounded-sm shadow-sm transition disabled:opacity-50">
                                Lưu nháp
                            </button>
                            <button @click="submitForm('published')" :disabled="form.processing" type="button" class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-2.5 px-10 rounded-sm shadow-sm transition disabled:opacity-50">
                                Mở đăng ký
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>