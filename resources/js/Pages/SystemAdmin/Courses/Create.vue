<script setup>
import { ref, watch, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    approvedRequests: Array,
    preselectedRequestId: String // ID truyền sang nếu bấm "+ Tạo khóa học" từ màn Duyệt
});

// Quản lý trạng thái nguồn tạo khóa học
const sourceType = ref('request'); // 'request' (Từ yêu cầu) hoặc 'internal' (Phòng đào tạo đề xuất)
const selectedRequestId = ref(props.preselectedRequestId || '');

// Khởi tạo Form gửi đi
const form = useForm({
    request_ids: [],
    reason: '',
    name: '',
    content: '',
    target_audience: 'Toàn phòng',
    format: 'Offline', // Mặc định Offline để khớp validation
    duration: '', 
    instructor: '',
    notes: '',
    description: ''
});

// Tự động điền (Auto-fill) nội dung khóa học nếu chọn từ Yêu cầu đã duyệt
const autoFillFromRequest = (reqId) => {
    if (reqId) {
        const req = props.approvedRequests.find(r => r.id == reqId);
        if (req) {
            form.name = req.course_name;
            form.content = req.content;
            form.target_audience = req.target_audience;
            form.duration = req.expected_duration;
        }
    }
};

// Theo dõi nếu Admin chọn Yêu cầu khác ở Dropdown
watch(selectedRequestId, (newId) => {
    autoFillFromRequest(newId);
});

// Chạy một lần lúc load trang (Phục vụ trường hợp có preselectedRequestId)
onMounted(() => {
    if (selectedRequestId.value) {
        sourceType.value = 'request';
        autoFillFromRequest(selectedRequestId.value);
    } else if (props.approvedRequests.length === 0) {
        sourceType.value = 'internal'; // Tự động nhảy sang Internal nếu không có YC nào
    }
});

// Submit Form
const submitForm = () => {
    // Tiền xử lý dữ liệu trước khi ném lên server
    if (sourceType.value === 'request') {
        form.request_ids = selectedRequestId.value ? [selectedRequestId.value] : [];
        form.reason = ''; // Xóa lý do
    } else {
        form.request_ids = [];
    }

    form.post(route('system.courses.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Tạo khóa học" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('system.courses.index')" class="font-bold text-xl text-blue-600 hover:underline">
                &lt; Quay lại danh sách Khóa học
            </Link>
        </template>

        <div class="py-6">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                    {{ $page.props.flash.error }}
                </div>

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

                    <form @submit.prevent="submitForm">
                        
                        <div class="mb-10">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">1. Thông tin nguồn:</h3>
                            <div class="ml-6">
                                <div v-if="sourceType === 'request'" class="flex flex-col gap-1">
                                    <div class="flex items-center gap-4">
                                        <label class="text-sm font-bold text-gray-700 w-44">Chọn yêu cầu đã duyệt:</label>
                                        <select v-model="selectedRequestId" class="border-gray-400 rounded-sm text-sm w-96 focus:ring-gray-500">
                                            <option value="">-- Chọn yêu cầu --</option>
                                            <option v-for="req in approvedRequests" :key="req.id" :value="req.id">
                                                {{ req.code }} - {{ req.department?.name }} ({{ req.course_name }})
                                            </option>
                                        </select>
                                    </div>
                                    <div v-if="form.errors.request_ids" class="text-red-500 text-xs mt-1 ml-48">{{ form.errors.request_ids }}</div>
                                    <div v-if="approvedRequests.length === 0" class="text-orange-500 text-xs mt-1 ml-48 italic">
                                        Hiện chưa có yêu cầu nào đang ở trạng thái Đã duyệt.
                                    </div>
                                </div>
                                
                                <div v-else class="flex flex-col gap-1">
                                    <div class="flex items-start gap-4">
                                        <label class="text-sm font-bold text-gray-700 mt-2 w-44">Lý do đề xuất khóa học: <span class="text-red-500">*</span></label>
                                        <input v-model="form.reason" type="text" placeholder="Ví dụ: đào tạo định kỳ, yêu cầu BGĐ, chiến lược..." class="border-gray-400 rounded-sm text-sm flex-1 focus:ring-gray-500 placeholder-gray-400">
                                    </div>
                                    <div v-if="form.errors.reason" class="text-red-500 text-xs mt-1 ml-48">{{ form.errors.reason }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">2. Thông tin khóa học:</h3>
                            <div class="grid grid-cols-2 gap-x-12 gap-y-6 ml-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Mã khóa học:</label>
                                    <input type="text" disabled value="Mã sẽ được tự động sinh (KH-2026-XXXX)" class="w-full bg-gray-100 border-gray-300 rounded-sm text-gray-500 text-sm cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Tên khóa học: <span class="text-red-500">*</span></label>
                                    <input v-model="form.name" type="text" placeholder="Nhập vào đây..." required class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Nội dung đào tạo:</label>
                                    <textarea v-model="form.content" rows="5" placeholder="Mô tả chi tiết nội dung cần học..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 resize-none"></textarea>
                                </div>
                                <div class="flex flex-col justify-between h-full">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Đối tượng / Phạm vi đào tạo: <span class="text-red-500">*</span></label>
                                        <select v-model="form.target_audience" required class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                            <option>Toàn phòng</option>
                                            <option>Cấp quản lý</option>
                                            <option>Nhân viên mới</option>
                                            <option>Toàn công ty</option>
                                        </select>
                                    </div>
                                    <div class="mt-4">
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Thời lượng dự kiến (Giờ): <span class="text-red-500">*</span></label>
                                        <input v-model="form.duration" type="number" min="1" placeholder="VD: 8" required class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                        <div v-if="form.errors.duration" class="text-red-500 text-xs mt-1">{{ form.errors.duration }}</div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Hình thức đào tạo: <span class="text-red-500">*</span></label>
                                    <select v-model="form.format" required class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                        <option value="Offline">Offline (Tại lớp)</option>
                                        <option value="Online">Online (Zoom/Teams)</option>
                                        <option value="Hybrid">Hybrid (Kết hợp)</option>
                                    </select>
                                    <div v-if="form.errors.format" class="text-red-500 text-xs mt-1">{{ form.errors.format }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Ghi chú thêm (nếu có):</label>
                                    <input v-model="form.notes" type="text" placeholder="Nhập vào đây..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Giảng viên (Dự kiến):</label>
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
                            <button type="submit" :disabled="form.processing" class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-2.5 px-10 rounded-sm shadow-sm transition disabled:opacity-50">
                                Tạo khóa học
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>