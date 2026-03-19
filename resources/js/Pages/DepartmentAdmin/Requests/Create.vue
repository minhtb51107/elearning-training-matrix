<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gửi yêu cầu đào tạo:</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                    
                    <div class="bg-gray-100 p-4 rounded mb-6">
                        <h3 class="font-bold mb-3 border-b pb-2">Thông tin phòng ban:</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phòng ban:</label>
                                <input type="text" disabled :value="department_name" class="mt-1 block w-full bg-gray-200 border-gray-300 rounded-md text-gray-600 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Người gửi:</label>
                                <input type="text" disabled :value="requester_name" class="mt-1 block w-full bg-gray-200 border-gray-300 rounded-md text-gray-600 cursor-not-allowed">
                            </div>
                        </div>
                    </div>

                    <h3 class="font-bold mb-4">Thông tin yêu cầu đào tạo:</h3>
                    <form @submit.prevent>
                        <div class="grid grid-cols-2 gap-6 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tên khóa học <span class="text-red-500">*</span></label>
                                <input v-model="form.course_name" type="text" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="Nhập vào đây..." required>
                                <div v-if="form.errors.course_name" class="text-red-500 text-xs mt-1">{{ form.errors.course_name }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Đối tượng đào tạo đề xuất <span class="text-red-500">*</span></label>
                                <select v-model="form.target_audience" class="mt-1 block w-full border-gray-300 rounded-md">
                                    <option value="Toàn phòng">Toàn phòng</option>
                                    <option value="Cấp quản lý">Cấp quản lý</option>
                                    <option value="Nhân viên mới">Nhân viên mới</option>
                                    <option value="Cá nhân">Cá nhân</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nội dung đào tạo <span class="text-red-500">*</span></label>
                                <textarea v-model="form.content" rows="4" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="Mô tả chi tiết nội dung cần học..." required></textarea>
                                <div v-if="form.errors.content" class="text-red-500 text-xs mt-1">{{ form.errors.content }}</div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700">Thời lượng dự kiến</label>
                                    <input v-model="form.expected_duration" type="text" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="VD: 8 giờ, 2 ngày...">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ghi chú thêm (nếu có)</label>
                                    <input v-model="form.notes" type="text" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="Nhập vào đây...">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center gap-4 mt-8 pt-4 border-t">
                            <Link :href="route('department.requests.index')" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium">
                                [ HỦY ]
                            </Link>
                            <button @click="submitForm('draft')" type="button" :disabled="form.processing" class="px-6 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 font-medium">
                                [ LƯU VÀO NHÁP ]
                            </button>
                            <button @click="submitForm('pending')" type="button" :disabled="form.processing" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                                [ GỬI YÊU CẦU ]
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>