<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    trainingRequest: Object,
});

// Hàm format dữ liệu để hiển thị đẹp như Mockup
const formatStatus = (status) => {
    const labels = {
        'pending': 'ĐANG CHỜ DUYỆT',
        'approved': 'ĐÃ DUYỆT',
        'processed': 'ĐÃ XỬ LÝ',
        'rejected': 'TỪ CHỐI'
    };
    return labels[status] || status.toUpperCase();
};

const formatDate = (dateStr) => {
    if (!dateStr) return '--';
    return new Date(dateStr).toLocaleDateString('vi-VN');
};

// State Quản lý Modals
const showApproveModal = ref(false);
const showRejectModal = ref(false);

// State Form dùng chung cho Inertia (Gửi lên Server)
const actionForm = useForm({
    status: '',
    hr_feedback: ''
});

// State Form Local (Chỉ dùng lưu tạm text trên giao diện trước khi gửi)
const approveFormLocal = ref({
    created_course_name: props.trainingRequest.course_name, // Gợi ý tên dựa trên yêu cầu
    note: ''
});

const rejectFormLocal = ref({
    reason: ''
});

// Hàm bật tắt
const openApproveModal = () => showApproveModal.value = true;
const closeApproveModal = () => {
    showApproveModal.value = false;
    actionForm.clearErrors();
};

const openRejectModal = () => showRejectModal.value = true;
const closeRejectModal = () => {
    showRejectModal.value = false;
    actionForm.clearErrors();
};

// Các hàm Submit gọi API (Database)
const submitApprove = () => {
    actionForm.status = 'approved';
    actionForm.hr_feedback = approveFormLocal.value.note; // Ghi chú thêm nếu có
    
    actionForm.put(route('system.requests.update-status', props.trainingRequest.id), {
        onSuccess: () => closeApproveModal(),
        preserveScroll: true
    });
};

const submitReject = () => {
    actionForm.status = 'rejected';
    actionForm.hr_feedback = rejectFormLocal.value.reason;
    
    actionForm.put(route('system.requests.update-status', props.trainingRequest.id), {
        onSuccess: () => closeRejectModal(),
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="'Chi tiết Yêu cầu: ' + trainingRequest.code" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('system.requests.index')" class="font-bold text-xl text-blue-600 hover:underline">
                &lt; Quay lại danh sách
            </Link>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                    {{ $page.props.flash.error }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Chi tiết yêu cầu đào tạo</h2>

                    <div class="mb-8">
                        <h3 class="font-bold text-[15px] text-gray-800 mb-3">Thông tin chung:</h3>
                        <div class="bg-[#e5e7eb] p-6 rounded-sm text-[15px]">
                            <div class="grid grid-cols-[120px_1fr] gap-y-3 text-gray-800">
                                <span class="font-bold">Mã yêu cầu:</span> <span>{{ trainingRequest.code }}</span>
                                <span class="font-bold">Phòng ban:</span> <span>{{ trainingRequest.department?.name }}</span>
                                <span class="font-bold">Người gửi:</span> <span>{{ trainingRequest.requester?.name }}</span>
                                <span class="font-bold">Ngày gửi:</span> <span>{{ formatDate(trainingRequest.created_at) }}</span>
                                <span class="font-bold">Trạng thái:</span> 
                                <span :class="{'text-yellow-600': trainingRequest.status === 'pending', 'text-green-600': trainingRequest.status === 'approved', 'text-red-600': trainingRequest.status === 'rejected'}" class="uppercase tracking-wide font-bold">
                                    {{ formatStatus(trainingRequest.status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="font-bold text-[15px] text-gray-800 mb-3">Thông tin yêu cầu đào tạo:</h3>
                        
                        <div class="grid grid-cols-2 gap-6 mb-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tên khóa học:</label>
                                <input :value="trainingRequest.course_name" type="text" disabled class="w-full bg-gray-50 border-gray-300 rounded text-gray-600">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Đối tượng / Phạm vi đào tạo:</label>
                                <select disabled class="w-full bg-gray-50 border-gray-300 rounded text-gray-600 appearance-none">
                                    <option>{{ trainingRequest.target_audience }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6 mb-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nội dung đào tạo:</label>
                                <textarea :value="trainingRequest.content" rows="4" disabled class="w-full bg-gray-50 border-gray-300 rounded text-gray-600 resize-none italic"></textarea>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Thời lượng dự kiến:</label>
                                    <input :value="trainingRequest.expected_duration + ' giờ'" type="text" disabled class="w-full bg-gray-50 border-gray-300 rounded text-gray-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Ghi chú thêm (nếu có):</label>
                                    <input :value="trainingRequest.notes" type="text" disabled class="w-full bg-gray-50 border-gray-300 rounded text-gray-400 placeholder-gray-400" placeholder="(Không có ghi chú)">
                                </div>
                            </div>
                        </div>

                        <div v-if="trainingRequest.hr_feedback || trainingRequest.status === 'rejected'">
                            <label class="block text-sm font-bold text-red-600 mb-1">Phản hồi từ phòng Đào Tạo:</label>
                            <input :value="trainingRequest.hr_feedback" type="text" disabled class="w-full bg-red-50 border-red-300 text-red-700 rounded placeholder-red-300 italic" placeholder="(Chưa có phản hồi)">
                        </div>
                    </div>

                    <div v-if="trainingRequest.status === 'pending'" class="flex justify-center gap-6 mt-8">
                        <button @click="openRejectModal" class="bg-[#c93b42] hover:bg-red-800 text-white font-bold py-2.5 px-12 rounded shadow transition">
                            Từ chối
                        </button>
                        <button @click="openApproveModal" class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-2.5 px-12 rounded shadow transition">
                            Duyệt
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <Modal :show="showApproveModal" @close="closeApproveModal" maxWidth="md">
            <div class="p-6 bg-white border-2 border-gray-300">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Duyệt yêu cầu đào tạo</h2>
                
                <div class="bg-gray-200 p-4 mb-4 text-sm border border-gray-300">
                    <div class="grid grid-cols-[120px_1fr] gap-y-1">
                        <span class="font-bold text-gray-700">Mã yêu cầu:</span> <span>{{ trainingRequest.code }}</span>
                        <span class="font-bold text-gray-700">Phòng ban:</span> <span>{{ trainingRequest.department?.name }}</span>
                        <span class="font-bold text-gray-700">Tên yêu cầu:</span> <span>{{ trainingRequest.course_name }}</span>
                    </div>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="grid grid-cols-[150px_1fr] items-center">
                        <label class="text-sm font-bold text-gray-800">Tên khóa học sẽ tạo:</label>
                        <input v-model="approveFormLocal.created_course_name" type="text" class="w-full border-gray-400 rounded-sm shadow-sm py-1.5 text-sm">
                    </div>
                    <div class="grid grid-cols-[150px_1fr] items-center">
                        <label class="text-sm font-bold text-gray-800">Ghi chú (optional):</label>
                        <input v-model="approveFormLocal.note" type="text" class="w-full border-gray-400 rounded-sm shadow-sm py-1.5 text-sm placeholder-gray-400" placeholder="Xin mời nhập vào đây...">
                    </div>
                </div>

                <p class="text-center text-[15px] text-gray-800 italic mb-6">Bạn có chắc chắn muốn <span class="font-bold text-green-600">DUYỆT</span> yêu cầu đào tạo này không?</p>

                <div class="flex justify-center gap-6">
                    <button @click="closeApproveModal" :disabled="actionForm.processing" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-10 rounded shadow transition disabled:opacity-50">Hủy</button>
                    <button @click="submitApprove" :disabled="actionForm.processing" class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-2 px-10 rounded shadow transition disabled:opacity-50">Xác nhận Duyệt</button>
                </div>
            </div>
        </Modal>

        <Modal :show="showRejectModal" @close="closeRejectModal" maxWidth="md">
            <div class="p-6 bg-white border-2 border-gray-300">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Từ chối yêu cầu đào tạo</h2>
                
                <div class="bg-gray-200 p-4 mb-4 text-sm border border-gray-300">
                    <div class="grid grid-cols-[120px_1fr] gap-y-1">
                        <span class="font-bold text-gray-700">Mã yêu cầu:</span> <span>{{ trainingRequest.code }}</span>
                        <span class="font-bold text-gray-700">Phòng ban:</span> <span>{{ trainingRequest.department?.name }}</span>
                        <span class="font-bold text-gray-700">Tên yêu cầu:</span> <span>{{ trainingRequest.course_name }}</span>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-800 mb-1">Lý do từ chối: <span class="text-red-500">*</span></label>
                    <textarea v-model="rejectFormLocal.reason" rows="3" class="w-full border-gray-400 rounded-sm shadow-sm text-sm placeholder-gray-400" placeholder="VD: Trùng nội dung đào tạo hiện có / Không ưu tiên Q1..."></textarea>
                    <div v-if="actionForm.errors.hr_feedback" class="text-red-600 text-xs mt-1 font-bold">{{ actionForm.errors.hr_feedback }}</div>
                </div>

                <p class="text-center text-[15px] text-gray-800 italic mb-6">Bạn có chắc chắn muốn <span class="font-bold text-red-600">TỪ CHỐI</span> yêu cầu đào tạo này không?</p>

                <div class="flex justify-center gap-6">
                    <button @click="closeRejectModal" :disabled="actionForm.processing" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-10 rounded shadow transition disabled:opacity-50">Hủy</button>
                    <button @click="submitReject" :disabled="actionForm.processing" class="bg-[#c93b42] hover:bg-red-800 text-white font-bold py-2 px-10 rounded shadow transition disabled:opacity-50">Từ chối</button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>