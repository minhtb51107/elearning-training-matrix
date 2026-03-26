<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
// Tích hợp Heroicons
import { ArrowLeftIcon, PaperAirplaneIcon, DocumentTextIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    department_name: String,
    requester_name: String,
});

const form = useForm({
    course_name: '',
    target_audience: 'Toàn phòng',
    content: '',
    expected_duration: '',
    notes: '',
    action: 'pending', // mặc định là gửi duyệt
});

const submitForm = (actionType) => {
    form.action = actionType;
    form.post(route('department.requests.store'));
};
</script>

<template>
    <Head title="Gửi yêu cầu đào tạo" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-4">
                    <Link :href="route('department.requests.index')" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                        <ArrowLeftIcon class="w-4 h-4 mr-1" />
                        Quay lại danh sách
                    </Link>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    
                    <div class="px-6 py-5 border-b border-gray-200 bg-gray-50/50">
                        <h2 class="text-xl font-bold text-gray-900">Tạo yêu cầu đào tạo mới</h2>
                        <p class="text-sm text-gray-500 mt-1">Điền các thông tin chi tiết để đề xuất mở khóa học cho bộ phận của bạn.</p>
                    </div>

                    <div class="p-6 sm:p-8">
                        <div class="bg-blue-50/50 border border-blue-100 p-5 rounded-xl mb-8">
                            <h3 class="text-sm font-bold text-blue-800 mb-4 uppercase tracking-wider">Thông tin người đề xuất</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-600 mb-1">Phòng ban</label>
                                    <input type="text" disabled :value="department_name" class="block w-full bg-white border-gray-200 rounded-lg text-gray-500 cursor-not-allowed text-sm shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-600 mb-1">Người gửi (Admin)</label>
                                    <input type="text" disabled :value="requester_name" class="block w-full bg-white border-gray-200 rounded-lg text-gray-500 cursor-not-allowed text-sm shadow-sm">
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent>
                            <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">Nội dung đề xuất</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1">Tên khóa học <span class="text-red-500">*</span></label>
                                    <input v-model="form.course_name" type="text" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm transition-shadow" placeholder="VD: Kỹ năng giao tiếp..." required>
                                    <div v-if="form.errors.course_name" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.course_name }}</div>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1">Đối tượng đào tạo <span class="text-red-500">*</span></label>
                                    <select v-model="form.target_audience" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm transition-shadow">
                                        <option value="Toàn phòng">Toàn phòng ban</option>
                                        <option value="Cấp quản lý">Chỉ cấp quản lý</option>
                                        <option value="Nhân viên mới">Nhân viên mới (Onboarding)</option>
                                        <option value="Cá nhân">Cá nhân chỉ định</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="md:col-span-2">
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1">Nội dung đào tạo chi tiết <span class="text-red-500">*</span></label>
                                    <textarea v-model="form.content" rows="4" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm transition-shadow" placeholder="Mô tả cụ thể những kiến thức cần học, mục tiêu sau khóa học..." required></textarea>
                                    <div v-if="form.errors.content" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.content }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1">Thời lượng dự kiến (Giờ) <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input v-model="form.expected_duration" type="number" min="1" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm transition-shadow pr-12" placeholder="VD: 8" required>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400 text-sm font-medium">Giờ</div>
                                    </div>
                                    <div v-if="form.errors.expected_duration" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.expected_duration }}</div>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1">Ghi chú thêm (Tùy chọn)</label>
                                    <input v-model="form.notes" type="text" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm transition-shadow" placeholder="Ghi chú thêm cho phòng Đào tạo...">
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mt-10 pt-6 border-t border-gray-100">
                                <Link :href="route('department.requests.index')" class="w-full sm:w-auto px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-colors text-center">
                                    Hủy bỏ
                                </Link>
                                <button @click="submitForm('draft')" type="button" :disabled="form.processing" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 border border-transparent rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors disabled:opacity-70">
                                    <DocumentTextIcon class="w-4 h-4 text-gray-500" />
                                    Lưu bản nháp
                                </button>
                                <button @click="submitForm('pending')" type="button" :disabled="form.processing" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm shadow-blue-600/30 transition-all disabled:opacity-70">
                                    <PaperAirplaneIcon class="w-4 h-4" />
                                    Gửi yêu cầu
                                </button>
                            </div>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>