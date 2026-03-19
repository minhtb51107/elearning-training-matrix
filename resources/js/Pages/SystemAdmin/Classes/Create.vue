<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const form = ref({
    course_id: 'TRN-2026-SALES-01',
    code: '[ CLS-2026-SALES-01-01 ]',
    name: '',
    instructor: '',
    duration: '',
    start_date: '',
    end_date: '',
    department: 'Phòng Kinh doanh',
    roles: 'Team lead',
    capacity: '',
    format: ''
});

const mockCourses = [
    { id: 'TRN-2026-SALES-01', text: 'TRN-2026-SALES-01 - Kỹ năng bán hàng' },
    { id: 'TRN-2026-IT-02', text: 'TRN-2026-IT-02 - DevOps cơ bản' }
];
</script>

<template>
    <Head title="Tạo lớp học" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-8 border-b pb-4">Tạo lớp học:</h2>

                    <form @submit.prevent>
                        
                        <div class="flex items-center justify-center gap-4 mb-10 border-b border-gray-200 pb-8">
                            <label class="text-base font-bold text-gray-800">Chọn khóa học:</label>
                            <select v-model="form.course_id" class="border-gray-400 rounded-sm text-[15px] w-[400px] focus:ring-gray-500 py-2">
                                <option v-for="course in mockCourses" :key="course.id" :value="course.id">{{ course.text }}</option>
                            </select>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">1. Thông tin lớp học:</h3>
                            <div class="grid grid-cols-2 gap-x-12 gap-y-6 ml-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Mã lớp học:</label>
                                    <div class="flex items-center gap-2">
                                        <input v-model="form.code" type="text" disabled class="w-full bg-gray-50 border-gray-300 rounded-sm text-gray-500 text-sm cursor-not-allowed">
                                        <span class="text-xs text-gray-500">(auto)</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Giảng viên:</label>
                                    <select v-model="form.instructor" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                        <option value=""></option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Tên lớp học:</label>
                                    <input v-model="form.name" type="text" placeholder="Nhập vào đây..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 placeholder-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Thời lượng:</label>
                                    <select v-model="form.duration" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                        <option value=""></option>
                                    </select>
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Thời gian học:</label>
                                    <div class="flex gap-8">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-bold text-gray-700 w-16">Từ ngày:</span>
                                            <input v-model="form.start_date" type="text" class="border-gray-400 rounded-sm text-sm focus:ring-gray-500 w-48">
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-bold text-gray-700">Đến ngày:</span>
                                            <input v-model="form.end_date" type="text" class="border-gray-400 rounded-sm text-sm focus:ring-gray-500 w-48">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-12">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">2. Phạm vi & Đối tượng:</h3>
                            <div class="grid grid-cols-2 gap-x-12 gap-y-6 ml-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Phòng ban:</label>
                                    <select v-model="form.department" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                        <option>Phòng Kinh doanh</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Số lượng tối đa:</label>
                                    <input v-model="form.capacity" type="text" placeholder="Nhập vào đây..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 placeholder-gray-300">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Vai trò công việc:</label>
                                    <div class="relative">
                                        <select v-model="form.roles" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 appearance-none">
                                            <option>Team lead</option>
                                        </select>
                                        <span class="absolute right-8 top-1/2 -translate-y-1/2 text-xs text-gray-500">(CHỌN NHIỀU)</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Hình thức:</label>
                                    <input v-model="form.format" type="text" placeholder="Nhập vào đây..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 placeholder-gray-300">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center gap-6 mt-8 pt-8">
                            <Link :href="route('system.classes.index')" class="bg-[#c93b42] hover:bg-red-800 text-white font-bold py-2.5 px-12 rounded-sm shadow-sm transition">
                                Hủy
                            </Link>
                            <button type="button" class="bg-[#e5e7eb] hover:bg-gray-300 text-gray-900 border border-gray-400 font-bold py-2.5 px-8 rounded-sm shadow-sm transition">
                                Lưu nháp
                            </button>
                            <button type="button" class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-2.5 px-10 rounded-sm shadow-sm transition">
                                Tạo lớp học
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>