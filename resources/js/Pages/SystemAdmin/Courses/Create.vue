<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Quản lý trạng thái nguồn tạo khóa học
const sourceType = ref('request'); // 'request' (Từ yêu cầu) hoặc 'internal' (Phòng đào tạo đề xuất)

const form = ref({
    request_id: 'YC-2026-KD-001',
    internal_reason: '',
    code: 'KH-AUTO-2026-01', // Mã tự sinh
    name: '',
    content: '',
    target_audience: 'Toàn phòng',
    format: '',
    duration: '',
    instructor: '',
    notes: '',
    description: ''
});

// Mock dữ liệu danh sách yêu cầu đã duyệt
const approvedRequests = [
    { id: 'YC-2026-KD-001', text: 'YC-2026-KD-001 - Kỹ năng bán hàng' },
    { id: 'YC-2026-IT-002', text: 'YC-2026-IT-002 - Lập trình VueJS' },
    { id: 'YC-2026-HR-003', text: 'YC-2026-HR-003 - Kỹ năng phỏng vấn' }
];
</script>

<template>
    <Head title="Tạo khóa học" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-8 border-b pb-4">Tạo khóa học</h2>

                    <div class="flex border-b border-gray-300 mb-8 gap-8 px-4 text-[15px]">
                        <label 
                            class="flex items-center cursor-pointer pb-3" 
                            :class="sourceType === 'request' ? 'text-gray-900 font-bold border-b-[3px] border-gray-800' : 'text-gray-500 hover:text-gray-800'"
                        >
                            <input type="radio" v-model="sourceType" value="request" class="mr-2 border-gray-400 text-gray-800 focus:ring-gray-800"> 
                            Từ yêu cầu phòng ban
                        </label>
                        <label 
                            class="flex items-center cursor-pointer pb-3" 
                            :class="sourceType === 'internal' ? 'text-gray-900 font-bold border-b-[3px] border-gray-800' : 'text-gray-500 hover:text-gray-800'"
                        >
                            <input type="radio" v-model="sourceType" value="internal" class="mr-2 border-gray-400 text-gray-800 focus:ring-gray-800"> 
                            Phòng đào tạo đề xuất
                        </label>
                    </div>

                    <form @submit.prevent>
                        
                        <div class="mb-10">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">1. Thông tin nguồn:</h3>
                            <div class="ml-6">
                                <div v-if="sourceType === 'request'" class="flex items-center gap-4">
                                    <label class="text-sm font-bold text-gray-700 w-44">Chọn yêu cầu đã duyệt:</label>
                                    <select v-model="form.request_id" class="border-gray-400 rounded-sm text-sm w-96 focus:ring-gray-500">
                                        <option value="">-- Chọn yêu cầu --</option>
                                        <option v-for="req in approvedRequests" :key="req.id" :value="req.id">{{ req.text }}</option>
                                    </select>
                                </div>
                                
                                <div v-else class="flex items-start gap-4">
                                    <label class="text-sm font-bold text-gray-700 mt-2 w-44">Lý do đề xuất khóa học: <span class="text-red-500">*</span></label>
                                    <input v-model="form.internal_reason" type="text" placeholder="Ví dụ: đào tạo định kỳ, yêu cầu BGĐ, chiến lược..." class="border-gray-400 rounded-sm text-sm flex-1 focus:ring-gray-500 placeholder-gray-400">
                                </div>
                            </div>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">2. Thông tin khóa học:</h3>
                            <div class="grid grid-cols-2 gap-x-12 gap-y-6 ml-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Mã khóa học:</label>
                                    <input v-model="form.code" type="text" disabled class="w-full bg-gray-100 border-gray-300 rounded-sm text-gray-500 text-sm cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Tên khóa học:</label>
                                    <input v-model="form.name" type="text" placeholder="Nhập vào đây..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Nội dung đào tạo:</label>
                                    <textarea v-model="form.content" rows="5" placeholder="Mô tả chi tiết nội dung cần học..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 resize-none"></textarea>
                                </div>
                                <div class="flex flex-col justify-between h-full">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Đối tượng / Phạm vi đào tạo:</label>
                                        <select v-model="form.target_audience" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                            <option>Toàn phòng</option>
                                            <option>Cấp quản lý</option>
                                            <option>Nhân viên mới</option>
                                            <option>Toàn công ty</option>
                                        </select>
                                    </div>
                                    <div class="mt-4">
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Thời lượng dự kiến:</label>
                                        <input v-model="form.duration" type="text" placeholder="Nhập vào đây..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Hình thức đào tạo:</label>
                                    <input v-model="form.format" type="text" placeholder="Nhập vào đây..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Ghi chú thêm (nếu có):</label>
                                    <input v-model="form.notes" type="text" placeholder="Nhập vào đây..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Giảng viên:</label>
                                    <input v-model="form.instructor" type="text" placeholder="Nhập vào đây..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                </div>
                            </div>
                        </div>

                        <div class="mb-12">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">3. Mô tả & Tài liệu:</h3>
                            <div class="ml-6">
                                <div class="mb-6">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Mô tả khóa học:</label>
                                    <textarea v-model="form.description" rows="3" placeholder="Mô tả chi tiết..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 resize-none"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-3">Tài liệu khóa học:</label>
                                    <div class="flex gap-6 text-[#d97706] font-bold text-[15px]">
                                        <button type="button" class="hover:underline hover:text-orange-700 transition">[ Upload file ]</button>
                                        <button type="button" class="hover:underline hover:text-orange-700 transition">[ Link tài liệu ]</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center gap-10 mt-8 pt-6 border-t border-gray-200">
                            <Link :href="route('system.courses.index')" class="bg-[#c93b42] hover:bg-red-800 text-white font-bold py-2.5 px-16 rounded-sm shadow-sm transition">
                                Hủy
                            </Link>
                            <button type="button" class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-2.5 px-10 rounded-sm shadow-sm transition">
                                Tạo khóa học
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>