<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
// Tích hợp Heroicons
import { ArrowLeftIcon, DocumentTextIcon, PaperAirplaneIcon, InformationCircleIcon, ChatBubbleBottomCenterTextIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    trainingRequest: Object,
});

const isDraft = computed(() => props.trainingRequest.status === 'draft');

const form = useForm({
    course_name: props.trainingRequest.course_name,
    target_audience: props.trainingRequest.target_audience,
    content: props.trainingRequest.content,
    expected_duration: props.trainingRequest.expected_duration || '',
    notes: props.trainingRequest.notes || '',
    action: 'pending',
});

const submitUpdate = (actionType) => {
    form.action = actionType;
    form.put(route('department.requests.update', props.trainingRequest.id));
};

// Đổi màu Label dạng Pill
const getStatusBadge = (status) => {
    const badges = {
        'draft': { text: 'Nháp', class: 'bg-gray-100 text-gray-700 border-gray-200' },
        'pending': { text: 'Chờ duyệt', class: 'bg-yellow-50 text-yellow-700 border-yellow-200' },
        'approved': { text: 'Đã duyệt', class: 'bg-green-50 text-green-700 border-green-200' },
        'processed': { text: 'Đã xử lý', class: 'bg-blue-50 text-blue-700 border-blue-200' },
        'rejected': { text: 'Từ chối', class: 'bg-red-50 text-red-700 border-red-200' },
    };
    return badges[status] || { text: status, class: 'bg-gray-50 text-gray-600 border-gray-200' };
};
</script>

<template>
    <Head :title="'Chi tiết Yêu cầu: ' + trainingRequest.code" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-4">
                    <Link :href="route('department.requests.index')" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                        <ArrowLeftIcon class="w-4 h-4 mr-1" />
                        Quay lại danh sách
                    </Link>
                </div>
                
                <div v-if="$page.props.flash?.error" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm flex items-center gap-3">
                    <InformationCircleIcon class="w-5 h-5 text-red-500" />
                    <span class="font-medium">{{ $page.props.flash.error }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    
                    <div class="px-6 py-5 border-b border-gray-200 bg-gray-50/50 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Chi tiết yêu cầu đào tạo</h2>
                            <p class="text-sm text-gray-500 mt-1">Xem và chỉnh sửa thông tin yêu cầu của phòng ban.</p>
                        </div>
                        <span :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border', getStatusBadge(trainingRequest.status).class]">
                            {{ getStatusBadge(trainingRequest.status).text }}
                        </span>
                    </div>

                    <div class="p-6 sm:p-8">
                        
                        <div class="bg-blue-50/30 border border-blue-100 p-5 rounded-xl mb-8">
                            <h3 class="text-sm font-bold text-blue-800 mb-4 uppercase tracking-wider flex items-center gap-2">
                                <InformationCircleIcon class="w-4 h-4" />
                                Thông tin chung
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-y-4 gap-x-6 text-[13px]">
                                <div class="flex flex-col">
                                    <span class="text-gray-500 font-medium mb-1">Mã yêu cầu</span>
                                    <span class="font-semibold text-gray-900 text-sm">{{ trainingRequest.code }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-gray-500 font-medium mb-1">Phòng ban</span>
                                    <span class="font-semibold text-gray-900 text-sm">{{ trainingRequest.department?.name }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-gray-500 font-medium mb-1">Người gửi</span>
                                    <span class="font-semibold text-gray-900 text-sm">{{ trainingRequest.requester?.name }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-gray-500 font-medium mb-1">Ngày gửi</span>
                                    <span class="font-semibold text-gray-900 text-sm">{{ new Date(trainingRequest.created_at).toLocaleDateString('vi-VN') }}</span>
                                </div>
                            </div>
                        </div>

                        <div v-if="trainingRequest.hr_feedback" class="bg-red-50 border border-red-200 p-5 rounded-xl mb-8">
                            <h3 class="text-sm font-bold text-red-800 mb-3 uppercase tracking-wider flex items-center gap-2">
                                <ChatBubbleBottomCenterTextIcon class="w-4 h-4" />
                                Phản hồi từ phòng Đào Tạo
                            </h3>
                            <div class="text-sm text-red-700 italic bg-white/60 p-4 rounded-lg border border-red-100 shadow-sm">
                                "{{ trainingRequest.hr_feedback }}"
                            </div>
                        </div>

                        <form @submit.prevent>
                            <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">Nội dung đề xuất</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1">Tên khóa học <span v-if="isDraft" class="text-red-500">*</span></label>
                                    <input v-model="form.course_name" type="text" :disabled="!isDraft" :required="isDraft" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm transition-shadow disabled:bg-gray-50 disabled:text-gray-500 disabled:border-gray-200 disabled:shadow-none cursor-auto disabled:cursor-not-allowed">
                                    <div v-if="form.errors.course_name" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.course_name }}</div>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1">Đối tượng / Phạm vi <span v-if="isDraft" class="text-red-500">*</span></label>
                                    <select v-model="form.target_audience" :disabled="!isDraft" :required="isDraft" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm transition-shadow disabled:bg-gray-50 disabled:text-gray-500 disabled:border-gray-200 disabled:shadow-none cursor-auto disabled:cursor-not-allowed">
                                        <option value="Toàn phòng">Toàn phòng</option>
                                        <option value="Cấp quản lý">Cấp quản lý</option>
                                        <option value="Nhân viên mới">Nhân viên mới</option>
                                        <option value="Cá nhân">Cá nhân</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="md:col-span-2">
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1">Nội dung đào tạo <span v-if="isDraft" class="text-red-500">*</span></label>
                                    <textarea v-model="form.content" rows="4" :disabled="!isDraft" :required="isDraft" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm transition-shadow disabled:bg-gray-50 disabled:text-gray-500 disabled:border-gray-200 disabled:shadow-none cursor-auto disabled:cursor-not-allowed"></textarea>
                                    <div v-if="form.errors.content" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.content }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1">Thời lượng dự kiến (Giờ) <span v-if="isDraft" class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input v-model="form.expected_duration" type="number" min="1" :disabled="!isDraft" :required="isDraft" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm transition-shadow pr-12 disabled:bg-gray-50 disabled:text-gray-500 disabled:border-gray-200 disabled:shadow-none cursor-auto disabled:cursor-not-allowed" placeholder="VD: 8">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400 text-sm font-medium">Giờ</div>
                                    </div>
                                    <div v-if="form.errors.expected_duration" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.expected_duration }}</div>
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1">Ghi chú thêm</label>
                                    <input v-model="form.notes" type="text" :disabled="!isDraft" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm transition-shadow disabled:bg-gray-50 disabled:text-gray-500 disabled:border-gray-200 disabled:shadow-none cursor-auto disabled:cursor-not-allowed">
                                </div>
                            </div>

                            <div v-if="isDraft" class="flex flex-col sm:flex-row items-center justify-end gap-3 mt-10 pt-6 border-t border-gray-100">
                                <button @click="submitUpdate('draft')" type="button" :disabled="form.processing" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 border border-transparent rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors disabled:opacity-70">
                                    <DocumentTextIcon class="w-4 h-4 text-gray-500" />
                                    Cập nhật nháp
                                </button>
                                <button @click="submitUpdate('pending')" type="button" :disabled="form.processing" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm shadow-blue-600/30 transition-all disabled:opacity-70">
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