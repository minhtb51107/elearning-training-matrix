<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { ChevronLeftIcon, CheckIcon, XMarkIcon, ExclamationCircleIcon, CheckCircleIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    trainingRequest: Object,
});

// Hàm format dữ liệu để hiển thị đẹp
const formatStatus = (status) => {
    const labels = {
        'pending': 'Đang chờ duyệt',
        'approved': 'Đã duyệt',
        'processed': 'Đã xử lý',
        'rejected': 'Từ chối'
    };
    return labels[status] || status;
};

const formatDate = (dateStr) => {
    if (!dateStr) return '--';
    return new Date(dateStr).toLocaleDateString('vi-VN');
};

const getStatusBadge = (status) => {
    const badges = {
        'pending': 'bg-yellow-50 text-yellow-700 border-yellow-200',
        'approved': 'bg-green-50 text-green-700 border-green-200',
        'processed': 'bg-blue-50 text-blue-700 border-blue-200',
        'rejected': 'bg-red-50 text-red-700 border-red-200',
    };
    return badges[status] || 'bg-gray-50 text-gray-600 border-gray-200';
};

// State Quản lý Modals
const showApproveModal = ref(false);
const showRejectModal = ref(false);

// State Form dùng chung cho Inertia
const actionForm = useForm({
    status: '',
    hr_feedback: ''
});

// State Form Local
const approveFormLocal = ref({
    created_course_name: props.trainingRequest.course_name,
    note: ''
});

const rejectFormLocal = ref({
    reason: ''
});

// Hàm bật tắt Modals
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
    actionForm.hr_feedback = approveFormLocal.value.note;
    
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
            <Link :href="route('system.requests.index')" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                <ChevronLeftIcon class="w-4 h-4" />
                Quay lại danh sách
            </Link>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.error" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm flex items-center gap-3">
                    <ExclamationCircleIcon class="w-5 h-5 text-red-500" />
                    <span class="font-medium">{{ $page.props.flash.error }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    
                    <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Chi tiết yêu cầu đào tạo</h2>
                            <p class="text-sm text-gray-500 mt-1">Mã YC: <span class="font-semibold text-gray-700">{{ trainingRequest.code }}</span></p>
                        </div>
                        <span :class="['inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold border shadow-sm', getStatusBadge(trainingRequest.status)]">
                            {{ formatStatus(trainingRequest.status) }}
                        </span>
                    </div>

                    <div class="p-8">
                        <div class="mb-10">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Thông tin bộ phận gửi</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 rounded-xl p-5 border border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Phòng ban đề xuất</p>
                                    <p class="font-semibold text-gray-900">{{ trainingRequest.department?.name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Người đại diện gửi</p>
                                    <p class="font-semibold text-gray-900">{{ trainingRequest.requester?.name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Ngày gửi yêu cầu</p>
                                    <p class="font-semibold text-gray-900">{{ formatDate(trainingRequest.created_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Chi tiết chương trình học</h3>
                            
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tên khóa học đề xuất</label>
                                        <input :value="trainingRequest.course_name" type="text" disabled class="w-full bg-gray-50 border-gray-200 rounded-lg text-gray-700 font-medium shadow-sm focus:ring-0">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Đối tượng tham gia</label>
                                        <input :value="trainingRequest.target_audience" type="text" disabled class="w-full bg-gray-50 border-gray-200 rounded-lg text-gray-700 shadow-sm focus:ring-0">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nội dung / Mục tiêu đào tạo</label>
                                    <textarea :value="trainingRequest.content" rows="4" disabled class="w-full bg-gray-50 border-gray-200 rounded-lg text-gray-700 shadow-sm resize-none focus:ring-0"></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Thời lượng học dự kiến</label>
                                        <div class="relative">
                                            <input :value="trainingRequest.expected_duration" type="text" disabled class="w-full bg-gray-50 border-gray-200 rounded-lg text-gray-700 shadow-sm focus:ring-0 pr-12">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">Giờ</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Ghi chú thêm từ phòng ban</label>
                                        <input :value="trainingRequest.notes || 'Không có ghi chú'" type="text" disabled class="w-full bg-gray-50 border-gray-200 rounded-lg text-gray-500 italic shadow-sm focus:ring-0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="trainingRequest.hr_feedback || trainingRequest.status === 'rejected'" class="mt-8 pt-6 border-t border-gray-100">
                            <label class="block text-sm font-bold text-red-600 mb-2 flex items-center gap-2">
                                <ExclamationCircleIcon class="w-4 h-4" /> Phản hồi từ Bộ phận Đào tạo (HR)
                            </label>
                            <div class="bg-red-50/50 border border-red-100 rounded-lg p-4 text-red-700 text-sm">
                                {{ trainingRequest.hr_feedback || 'Không có phản hồi chi tiết.' }}
                            </div>
                        </div>

                        <div v-if="trainingRequest.status === 'pending'" class="flex justify-end items-center gap-4 mt-10 pt-6 border-t border-gray-100">
                            <button @click="openRejectModal" class="px-6 py-2.5 bg-white border border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300 rounded-lg font-semibold transition-all shadow-sm">
                                Từ chối yêu cầu
                            </button>
                            <button @click="openApproveModal" class="inline-flex items-center gap-2 px-6 py-2.5 bg-green-600 border border-transparent text-white hover:bg-green-700 rounded-lg font-semibold transition-all shadow-sm shadow-green-600/30">
                                <CheckIcon class="w-5 h-5" />
                                Phê duyệt
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showApproveModal" @close="closeApproveModal" maxWidth="md">
            <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
                <div class="px-6 py-4 bg-green-50 border-b border-green-100 flex items-center gap-3">
                    <div class="p-1.5 bg-green-100 rounded-full"><CheckCircleIcon class="w-5 h-5 text-green-600" /></div>
                    <h2 class="text-lg font-bold text-green-900">Phê duyệt yêu cầu</h2>
                </div>
                
                <div class="p-6">
                    <div class="mb-5 bg-gray-50 rounded-lg p-4 border border-gray-100 text-sm">
                        <p class="text-gray-500 mb-1">Khóa học đề xuất:</p>
                        <p class="font-bold text-gray-900 text-base">{{ trainingRequest.course_name }}</p>
                    </div>

                    <div class="space-y-4 mb-8">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tên khóa học sẽ tạo chính thức <span class="text-red-500">*</span></label>
                            <input v-model="approveFormLocal.created_course_name" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Ghi chú phản hồi (Tùy chọn)</label>
                            <textarea v-model="approveFormLocal.note" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 text-sm placeholder-gray-400" placeholder="VD: Khóa học sẽ được triển khai vào Quý 2..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button @click="closeApproveModal" :disabled="actionForm.processing" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Hủy bỏ
                        </button>
                        <button @click="submitApprove" :disabled="actionForm.processing" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-lg hover:bg-green-700 transition-colors shadow-sm disabled:opacity-50">
                            Xác nhận Duyệt
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <Modal :show="showRejectModal" @close="closeRejectModal" maxWidth="md">
            <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
                <div class="px-6 py-4 bg-red-50 border-b border-red-100 flex items-center gap-3">
                    <div class="p-1.5 bg-red-100 rounded-full"><ExclamationCircleIcon class="w-5 h-5 text-red-600" /></div>
                    <h2 class="text-lg font-bold text-red-900">Từ chối yêu cầu</h2>
                </div>
                
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">Bạn đang thực hiện thao tác từ chối yêu cầu khóa học <span class="font-bold text-gray-900">"{{ trainingRequest.course_name }}"</span> từ {{ trainingRequest.department?.name }}.</p>

                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Lý do từ chối <span class="text-red-500">*</span></label>
                        <textarea v-model="rejectFormLocal.reason" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 text-sm placeholder-gray-400" placeholder="VD: Trùng với chương trình đào tạo hiện có, hoặc chưa thuộc diện ưu tiên..."></textarea>
                        <div v-if="actionForm.errors.hr_feedback" class="text-red-600 text-xs mt-1.5 font-medium">{{ actionForm.errors.hr_feedback }}</div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button @click="closeRejectModal" :disabled="actionForm.processing" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Hủy bỏ
                        </button>
                        <button @click="submitReject" :disabled="actionForm.processing" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 transition-colors shadow-sm disabled:opacity-50">
                            Xác nhận Từ chối
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>