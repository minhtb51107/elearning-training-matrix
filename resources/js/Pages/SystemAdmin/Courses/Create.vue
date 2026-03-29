<script setup>
import { ref, watch, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeftIcon, DocumentArrowUpIcon, LinkIcon, XMarkIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    approvedRequests: Array,
    preselectedRequestId: String 
});

const sourceType = ref('request'); 
const selectedRequestId = ref(props.preselectedRequestId || '');

const form = useForm({
    request_ids: [],
    reason: '',
    name: '',
    target_audience: 'Toàn công ty',
    format: 'Offline', 
    duration: '', 
    instructor: '',
    notes: '',
    description: '',
    documents: []
});

const autoFillFromRequest = (reqId) => {
    if (reqId) {
        const req = props.approvedRequests.find(r => r.id == reqId);
        if (req) {
            form.name = req.course_name;
            form.target_audience = req.target_audience;
            form.duration = req.expected_duration;
        }
    }
};

watch(selectedRequestId, (newId) => { autoFillFromRequest(newId); });

watch(sourceType, (newType) => {
    if (newType === 'internal') form.target_audience = 'Toàn công ty';
    else if (newType === 'request' && selectedRequestId.value) autoFillFromRequest(selectedRequestId.value);
});

onMounted(() => {
    if (selectedRequestId.value) {
        sourceType.value = 'request';
        autoFillFromRequest(selectedRequestId.value);
    } else if (props.approvedRequests?.length === 0) {
        sourceType.value = 'internal'; 
    }
});

const fileInput = ref(null);
const showLinkInput = ref(false);
const newLink = ref({ title: '', url: '' });

const triggerFileInput = () => { fileInput.value.click(); };
const handleFileChange = (e) => {
    const files = e.target.files;
    for (let i = 0; i < files.length; i++) form.documents.push({ type: 'file', title: files[i].name, file: files[i] });
    e.target.value = null; 
};

const addLink = () => {
    if (newLink.value.url) {
        form.documents.push({ type: 'link', title: newLink.value.title || newLink.value.url, url: newLink.value.url });
        newLink.value = { title: '', url: '' }; 
        showLinkInput.value = false;
    }
};
const removeDocument = (index) => { form.documents.splice(index, 1); };

const submitForm = () => {
    if (sourceType.value === 'request') {
        form.request_ids = selectedRequestId.value ? [selectedRequestId.value] : [];
        form.reason = ''; 
    } else {
        form.request_ids = [];
    }
    form.post(route('system.courses.store'), { preserveScroll: true });
};
</script>

<template>
    <Head title="Tạo khóa học" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('system.courses.index')" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                <ChevronLeftIcon class="w-4 h-4" /> Quay lại danh sách Khóa học
            </Link>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.error" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm">
                    <span class="font-medium">{{ $page.props.flash.error }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 p-8">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Tạo khóa học mới</h2>
                        <p class="text-sm text-gray-500 mt-1">Bước 1: Thiết lập thông tin cơ bản. (Nội dung bài giảng sẽ được thêm ở bước sau)</p>
                    </div>

                    <form @submit.prevent="submitForm">
                        
                        <div class="mb-8 bg-gray-50/50 p-6 rounded-xl border border-gray-100">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">1. Nguồn tạo khóa học</h3>
                            <div class="flex gap-4 mb-6">
                                <button type="button" @click="sourceType = 'request'" :class="['px-6 py-2.5 rounded-lg text-sm font-semibold transition-all', sourceType === 'request' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50']">Từ Yêu cầu phòng ban</button>
                                <button type="button" @click="sourceType = 'internal'" :class="['px-6 py-2.5 rounded-lg text-sm font-semibold transition-all', sourceType === 'internal' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50']">Phòng đào tạo đề xuất</button>
                            </div>
                            <div v-if="sourceType === 'request'" class="max-w-2xl">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Chọn yêu cầu đã duyệt <span class="text-red-500">*</span></label>
                                <select v-model="selectedRequestId" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 text-sm">
                                    <option value="">-- Chọn yêu cầu --</option>
                                    <option v-for="req in approvedRequests" :key="req.id" :value="req.id">{{ req.code }} - {{ req.department?.name }} ({{ req.course_name }})</option>
                                </select>
                            </div>
                            <div v-else class="max-w-2xl">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Lý do đề xuất khóa học <span class="text-red-500">*</span></label>
                                <input v-model="form.reason" type="text" placeholder="Ví dụ: Đào tạo định kỳ..." class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 text-sm">
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">2. Thông tin chi tiết</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Mã khóa học</label><input type="text" disabled value="Hệ thống tự động sinh" class="w-full bg-gray-100 border-gray-200 rounded-lg text-gray-500 text-sm cursor-not-allowed"></div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Tên khóa học <span class="text-red-500">*</span></label><input v-model="form.name" type="text" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500"></div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phạm vi đào tạo <span class="text-red-500">*</span></label>
                                    <select v-model="form.target_audience" required :disabled="sourceType === 'request'" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 disabled:bg-gray-100">
                                        <template v-if="sourceType === 'request'"><option :value="form.target_audience">{{ form.target_audience }}</option></template>
                                        <template v-else><option value="Toàn công ty">Toàn công ty</option><option value="Cấp quản lý">Cấp quản lý</option><option value="Nhân viên mới">Nhân viên mới</option></template>
                                    </select>
                                </div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Thời lượng (Giờ) <span class="text-red-500">*</span></label><input v-model="form.duration" type="number" min="1" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500"></div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Hình thức <span class="text-red-500">*</span></label>
                                    <select v-model="form.format" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500"><option value="Offline">Offline (Tại lớp)</option><option value="Online">Online</option><option value="Hybrid">Hybrid</option></select>
                                </div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Giảng viên dự kiến</label><input v-model="form.instructor" type="text" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500"></div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">3. Mô tả & Tài liệu đính kèm</h3>
                            <div class="space-y-6">
                                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Mô tả khóa học</label><textarea v-model="form.description" rows="3" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500"></textarea></div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Đính kèm tài liệu chung</label>
                                    <div class="flex gap-4 mb-4">
                                        <input type="file" ref="fileInput" @change="handleFileChange" class="hidden" multiple>
                                        <button type="button" @click="triggerFileInput" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"><DocumentArrowUpIcon class="w-4 h-4 text-blue-600" /> Upload File</button>
                                        <button type="button" @click="showLinkInput = !showLinkInput" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"><LinkIcon class="w-4 h-4 text-blue-600" /> Thêm Link</button>
                                    </div>
                                    <div v-if="showLinkInput" class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4 flex gap-3 items-end">
                                        <div class="flex-1"><label class="block text-xs font-medium text-gray-500 mb-1">Tên tài liệu</label><input v-model="newLink.title" type="text" class="w-full border-gray-300 rounded-lg text-sm"></div>
                                        <div class="flex-1"><label class="block text-xs font-medium text-gray-500 mb-1">URL *</label><input v-model="newLink.url" type="url" class="w-full border-gray-300 rounded-lg text-sm"></div>
                                        <button type="button" @click="addLink" class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold">Lưu Link</button>
                                    </div>
                                    <div v-if="form.documents.length > 0" class="space-y-2">
                                        <div v-for="(doc, index) in form.documents" :key="index" class="flex justify-between items-center bg-gray-50 px-4 py-3 rounded-lg border border-gray-200">
                                            <div class="flex items-center gap-3">
                                                <div class="p-1.5 bg-white rounded shadow-sm"><DocumentArrowUpIcon v-if="doc.type === 'file'" class="w-5 h-5 text-gray-500" /><LinkIcon v-else class="w-5 h-5 text-blue-500" /></div>
                                                <div><p class="text-sm font-semibold text-gray-800">{{ doc.title }}</p><p class="text-xs text-gray-500">{{ doc.type === 'link' ? doc.url : 'File đính kèm' }}</p></div>
                                            </div>
                                            <button type="button" @click="removeDocument(index)" class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded"><XMarkIcon class="w-5 h-5" /></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-100">
                            <Link :href="route('system.courses.index')" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-semibold shadow-sm">Hủy bỏ</Link>
                            <button type="submit" :disabled="form.processing" class="px-8 py-2.5 bg-blue-600 text-white hover:bg-blue-700 rounded-lg font-semibold shadow-sm disabled:opacity-50 flex items-center gap-2">
                                <span v-if="form.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>Tạo Khóa Học
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>