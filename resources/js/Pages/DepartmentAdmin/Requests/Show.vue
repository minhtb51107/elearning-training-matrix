<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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

const getStatusLabel = (status) => {
    const labels = { 'draft': 'NHÁP', 'pending': 'ĐANG CHỜ DUYỆT', 'approved': 'ĐÃ DUYỆT', 'processed': 'ĐÃ XỬ LÝ', 'rejected': 'TỪ CHỐI' };
    return labels[status] || status.toUpperCase();
};
</script>

<template>
    <Head :title="'Chi tiết Yêu cầu: ' + trainingRequest.code" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('department.requests.index')" class="font-bold text-xl text-blue-600 hover:underline">
                &lt; Quay lại
            </Link>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                    
                    <div class="bg-gray-200 p-6 rounded-md mb-6 border border-gray-400">
                        <h3 class="font-bold mb-4">Thông tin chung:</h3>
                        <div class="grid grid-cols-[150px_1fr] gap-y-2 text-sm">
                            <span class="font-bold text-gray-700">Mã yêu cầu:</span> <span>{{ trainingRequest.code }}</span>
                            <span class="font-bold text-gray-700">Phòng ban:</span> <span>{{ trainingRequest.department?.name }}</span>
                            <span class="font-bold text-gray-700">Người gửi:</span> <span>{{ trainingRequest.requester?.name }}</span>
                            <span class="font-bold text-gray-700">Ngày gửi:</span> <span>{{ new Date(trainingRequest.created_at).toLocaleDateString('vi-VN') }}</span>
                            <span class="font-bold text-gray-700">Trạng thái:</span> <span class="font-bold text-blue-800">{{ getStatusLabel(trainingRequest.status) }}</span>
                        </div>
                    </div>

                    <h3 class="font-bold mb-4 border-b pb-2">Thông tin yêu cầu đào tạo:</h3>
                    
                    <form @submit.prevent>
                        <div class="grid grid-cols-2 gap-6 mb-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Tên khóa học:</label>
                                <input v-model="form.course_name" type="text" :disabled="!isDraft" class="mt-1 block w-full border-gray-300 rounded-md disabled:bg-gray-100 disabled:text-gray-600">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Đối tượng / Phạm vi:</label>
                                <select v-model="form.target_audience" :disabled="!isDraft" class="mt-1 block w-full border-gray-300 rounded-md disabled:bg-gray-100 disabled:text-gray-600">
                                    <option value="Toàn phòng">Toàn phòng</option>
                                    <option value="Cấp quản lý">Cấp quản lý</option>
                                    <option value="Nhân viên mới">Nhân viên mới</option>
                                    <option value="Cá nhân">Cá nhân</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Nội dung đào tạo:</label>
                                <textarea v-model="form.content" rows="4" :disabled="!isDraft" class="mt-1 block w-full border-gray-300 rounded-md disabled:bg-gray-100 disabled:text-gray-600"></textarea>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-bold text-gray-700">Thời lượng dự kiến:</label>
                                    <input v-model="form.expected_duration" type="text" :disabled="!isDraft" class="mt-1 block w-full border-gray-300 rounded-md disabled:bg-gray-100 disabled:text-gray-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Ghi chú thêm:</label>
                                    <input v-model="form.notes" type="text" :disabled="!isDraft" class="mt-1 block w-full border-gray-300 rounded-md disabled:bg-gray-100 disabled:text-gray-600">
                                </div>
                            </div>
                        </div>

                        <div v-if="trainingRequest.hr_feedback" class="mt-6 border-t pt-4">
                            <label class="block text-sm font-bold text-red-600 mb-2">Phản hồi từ phòng Đào Tạo:</label>
                            <div class="p-3 bg-red-50 border border-red-200 rounded text-red-800 italic">
                                {{ trainingRequest.hr_feedback }}
                            </div>
                        </div>

                        <div v-if="isDraft" class="flex justify-center gap-4 mt-8 pt-4 border-t">
                            <button @click="submitUpdate('draft')" type="button" :disabled="form.processing" class="px-6 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 font-medium">
                                [ CẬP NHẬT NHÁP ]
                            </button>
                            <button @click="submitUpdate('pending')" type="button" :disabled="form.processing" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                                [ GỬI YÊU CẦU ]
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>