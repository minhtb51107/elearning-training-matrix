<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeftIcon, DocumentArrowUpIcon, LinkIcon, XMarkIcon, DocumentTextIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    course: Object,
    departments: Array
});

const form = useForm({
    _method: 'put',
    name: props.course.name || '',
    target_audience: props.course.target_audience || 'Toàn công ty',
    format: props.course.format || 'Offline', 
    duration: props.course.duration || '', 
    instructor: props.course.instructor || '',
    notes: props.course.notes || '',
    description: props.course.description || '',
    reason: props.course.reason || '',
    new_documents: [],          
    deleted_document_ids: []   
});

const existingDocuments = ref(props.course.documents ? [...props.course.documents] : []);

const removeExistingDocument = (id, index) => {
    if(confirm('Tài liệu này sẽ bị xóa khỏi hệ thống sau khi bạn bấm Cập nhật. Xác nhận?')) {
        form.deleted_document_ids.push(id); 
        existingDocuments.value.splice(index, 1); 
    }
};

const fileInput = ref(null);
const showLinkInput = ref(false);
const newLink = ref({ title: '', url: '' });

const triggerFileInput = () => { fileInput.value.click(); };
const handleFileChange = (e) => {
    const files = e.target.files;
    for (let i = 0; i < files.length; i++) form.new_documents.push({ type: 'file', title: files[i].name, file: files[i] });
    e.target.value = null;
};

const addLink = () => {
    if (newLink.value.url) {
        form.new_documents.push({ type: 'link', title: newLink.value.title || newLink.value.url, url: newLink.value.url });
        newLink.value = { title: '', url: '' };
        showLinkInput.value = false;
    }
};

const removeNewDocument = (index) => { form.new_documents.splice(index, 1); };

const submitForm = () => {
    form.post(route('system.courses.update', props.course.id), { preserveScroll: true });
};
</script>

<template>
    <Head :title="'Sửa khóa học: ' + course.code" />

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
                    <div class="mb-8 flex justify-between items-end border-b border-gray-100 pb-5">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Cập nhật khóa học</h2>
                            <p class="text-sm text-gray-500 mt-1">Quản lý Giáo trình (Bài giảng/Trắc nghiệm) vui lòng vào trang Chi tiết khóa học.</p>
                        </div>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-md font-bold border border-blue-200 shadow-sm">{{ course.code }}</span>
                    </div>

                    <form @submit.prevent="submitForm">
                        
                        <div class="mb-8 bg-gray-50/50 p-6 rounded-xl border border-gray-100">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">1. Nguồn tạo khóa học</h3>
                            <div v-if="course.reason" class="max-w-2xl">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Phòng đào tạo đề xuất (Lý do)</label>
                                <input v-model="form.reason" type="text" class="w-full bg-gray-100 border-gray-200 rounded-lg shadow-sm text-gray-600 text-sm cursor-not-allowed font-medium" disabled>
                            </div>
                            <div v-else class="max-w-2xl">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nguồn từ</label>
                                <input type="text" value="Yêu cầu đào tạo từ phòng ban" class="w-full bg-gray-100 border-gray-200 rounded-lg shadow-sm text-gray-600 text-sm cursor-not-allowed font-medium" disabled>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">2. Thông tin chi tiết</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Mã khóa học</label><input type="text" disabled :value="course.code" class="w-full bg-gray-100 border-gray-200 rounded-lg text-gray-600 text-sm cursor-not-allowed font-bold"></div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Tên khóa học <span class="text-red-500">*</span></label><input v-model="form.name" type="text" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500"></div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phạm vi đào tạo <span class="text-red-500">*</span></label>
                                    <select v-model="form.target_audience" required :disabled="!course.reason" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 disabled:bg-gray-100">
                                        <template v-if="!course.reason"><option :value="form.target_audience">{{ form.target_audience }}</option></template>
                                        <template v-else><option value="Toàn công ty">Toàn công ty</option><option value="Cấp quản lý">Cấp quản lý</option><option value="Nhân viên mới">Nhân viên mới</option></template>
                                    </select>
                                </div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Thời lượng (Giờ) <span class="text-red-500">*</span></label><input v-model="form.duration" type="number" min="1" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500"></div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Hình thức <span class="text-red-500">*</span></label>
                                    <select v-model="form.format" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500"><option value="Offline">Offline (Tại lớp)</option><option value="Online">Online</option><option value="Hybrid">Hybrid</option></select>
                                </div>
                                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Giảng viên dự kiến</label><input v-model="form.instructor" type="text" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500"></div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">3. Mô tả & Tài liệu chung</h3>
                            <div class="space-y-6">
                                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Mô tả khóa học</label><textarea v-model="form.description" rows="3" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 resize-none"></textarea></div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Đính kèm tài liệu chung</label>
                                    <div class="flex gap-4 mb-4">
                                        <input type="file" ref="fileInput" @change="handleFileChange" class="hidden" multiple>
                                        <button type="button" @click="triggerFileInput" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"><DocumentArrowUpIcon class="w-4 h-4 text-amber-600" /> Upload File</button>
                                        <button type="button" @click="showLinkInput = !showLinkInput" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"><LinkIcon class="w-4 h-4 text-amber-600" /> Thêm Link</button>
                                    </div>

                                    <div v-if="showLinkInput" class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4 flex gap-3 items-end">
                                        <div class="flex-1"><label class="block text-xs font-medium text-gray-500 mb-1">Tên tài liệu</label><input v-model="newLink.title" type="text" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500"></div>
                                        <div class="flex-1"><label class="block text-xs font-medium text-gray-500 mb-1">URL *</label><input v-model="newLink.url" type="url" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500"></div>
                                        <button type="button" @click="addLink" class="px-5 py-2 bg-amber-500 text-white rounded-lg text-sm font-semibold">Lưu Link</button>
                                    </div>

                                    <div class="space-y-3 mt-6">
                                        <div v-if="existingDocuments.length > 0">
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Đang có trên hệ thống:</p>
                                            <div class="space-y-2">
                                                <div v-for="(doc, index) in existingDocuments" :key="doc.id" class="flex justify-between items-center bg-gray-50 px-4 py-3 rounded-lg border border-gray-200">
                                                    <div class="flex items-center gap-3">
                                                        <div class="p-1.5 bg-white rounded shadow-sm"><DocumentTextIcon v-if="doc.type === 'file'" class="w-5 h-5 text-gray-500" /><LinkIcon v-else class="w-5 h-5 text-blue-500" /></div>
                                                        <div><p class="text-sm font-semibold text-gray-800">{{ doc.title }}</p></div>
                                                    </div>
                                                    <button type="button" @click="removeExistingDocument(doc.id, index)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded"><XMarkIcon class="w-5 h-5" /></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="form.new_documents.length > 0" class="mt-4">
                                            <p class="text-xs font-bold text-green-500 uppercase tracking-wider mb-2">Tài liệu mới chuẩn bị thêm:</p>
                                            <div class="space-y-2">
                                                <div v-for="(doc, index) in form.new_documents" :key="index" class="flex justify-between items-center bg-green-50/50 px-4 py-3 rounded-lg border border-green-100">
                                                    <div class="flex items-center gap-3">
                                                        <div class="p-1.5 bg-white rounded shadow-sm"><DocumentArrowUpIcon v-if="doc.type === 'file'" class="w-5 h-5 text-green-500" /><LinkIcon v-else class="w-5 h-5 text-green-500" /></div>
                                                        <div><p class="text-sm font-semibold text-green-800">{{ doc.title }}</p></div>
                                                    </div>
                                                    <button type="button" @click="removeNewDocument(index)" class="p-1.5 text-green-400 hover:text-red-600 hover:bg-red-50 rounded"><XMarkIcon class="w-5 h-5" /></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-100">
                            <Link :href="route('system.courses.index')" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-semibold shadow-sm">Hủy bỏ</Link>
                            <button type="submit" :disabled="form.processing" class="px-8 py-2.5 bg-amber-500 text-white hover:bg-amber-600 rounded-lg font-semibold shadow-sm disabled:opacity-50 flex items-center gap-2">
                                <span v-if="form.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>Lưu Cập nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>