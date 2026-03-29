<script setup>
import { ref, watch, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeftIcon, DocumentArrowUpIcon, LinkIcon, XMarkIcon, PlusCircleIcon } from '@heroicons/vue/20/solid';

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
    // Sửa giá trị mặc định cho hợp với HR
    target_audience: 'Toàn công ty',
    format: 'Offline', 
    duration: '', 
    instructor: '',
    notes: '',
    description: '',
    lessons: [
        { title: '', media_type: 'youtube', media_url: '', file: null, duration: '' }
    ],
    assignments: [],
    documents: []
});

const addLesson = () => {
    form.lessons.push({ title: '', media_type: 'youtube', media_url: '', file: null, duration: '' });
};

const removeLesson = (index) => {
    if (form.lessons.length > 1) {
        form.lessons.splice(index, 1);
    } else {
        alert('Phải có ít nhất 1 bài giảng trong khóa học!');
    }
};

const addAssignment = () => {
    form.assignments.push({ title: '', type: 'final', questions: [''], pass_score: 50 });
};

const removeAssignment = (index) => {
    form.assignments.splice(index, 1);
};

const autoFillFromRequest = (reqId) => {
    if (reqId) {
        const req = props.approvedRequests.find(r => r.id == reqId);
        if (req) {
            form.name = req.course_name;
            form.lessons[0].title = req.content ? 'Tổng quan khóa học' : '';
            form.target_audience = req.target_audience;
            form.duration = req.expected_duration;
        }
    }
};

watch(selectedRequestId, (newId) => { autoFillFromRequest(newId); });

// RESET LẠI TARGET AUDIENCE KHI ĐỔI NGUỒN TẠO
watch(sourceType, (newType) => {
    if (newType === 'internal') {
        form.target_audience = 'Toàn công ty';
    } else if (newType === 'request' && selectedRequestId.value) {
        autoFillFromRequest(selectedRequestId.value);
    }
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
    for (let i = 0; i < files.length; i++) {
        form.documents.push({ type: 'file', title: files[i].name, file: files[i] });
    }
    e.target.value = null; 
};

const addLink = () => {
    if (newLink.value.url) {
        form.documents.push({ 
            type: 'link', 
            title: newLink.value.title || newLink.value.url, 
            url: newLink.value.url 
        });
        newLink.value = { title: '', url: '' }; 
        showLinkInput.value = false;
    }
};

const removeDocument = (index) => {
    form.documents.splice(index, 1);
};

const submitForm = () => {
    if (sourceType.value === 'request') {
        form.request_ids = selectedRequestId.value ? [selectedRequestId.value] : [];
        form.reason = ''; 
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
            <Link :href="route('system.courses.index')" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                <ChevronLeftIcon class="w-4 h-4" />
                Quay lại danh sách Khóa học
            </Link>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.error" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm">
                    <span class="font-medium">{{ $page.props.flash.error }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 p-8 sm:p-10">
                    
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Tạo khóa học mới</h2>
                        <p class="text-sm text-gray-500 mt-1">Thiết lập chương trình đào tạo từ yêu cầu của phòng ban hoặc đề xuất nội bộ.</p>
                    </div>

                    <form @submit.prevent="submitForm">
                        
                        <div class="mb-10 bg-gray-50/50 p-6 rounded-xl border border-gray-100">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">1. Nguồn tạo khóa học</h3>
                            
                            <div class="flex gap-4 mb-6">
                                <button type="button" @click="sourceType = 'request'" :class="['px-6 py-2.5 rounded-lg text-sm font-semibold transition-all', sourceType === 'request' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50']">
                                    Từ Yêu cầu phòng ban
                                </button>
                                <button type="button" @click="sourceType = 'internal'" :class="['px-6 py-2.5 rounded-lg text-sm font-semibold transition-all', sourceType === 'internal' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50']">
                                    Phòng đào tạo đề xuất
                                </button>
                            </div>

                            <div v-if="sourceType === 'request'" class="max-w-2xl">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Chọn yêu cầu đã duyệt <span class="text-red-500">*</span></label>
                                <select v-model="selectedRequestId" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option value="">-- Chọn yêu cầu --</option>
                                    <option v-for="req in approvedRequests" :key="req.id" :value="req.id">
                                        {{ req.code }} - {{ req.department?.name }} ({{ req.course_name }})
                                    </option>
                                </select>
                                <div v-if="approvedRequests?.length === 0" class="text-orange-600 text-xs mt-2 bg-orange-50 p-2 rounded border border-orange-100">
                                    Hiện chưa có yêu cầu nào đang ở trạng thái Đã duyệt.
                                </div>
                            </div>
                            
                            <div v-else class="max-w-2xl">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Lý do đề xuất khóa học <span class="text-red-500">*</span></label>
                                <input v-model="form.reason" type="text" placeholder="Ví dụ: Đào tạo định kỳ, Yêu cầu từ BGĐ..." class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm placeholder-gray-400">
                            </div>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">2. Thông tin chi tiết</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Mã khóa học</label>
                                    <input type="text" disabled value="Hệ thống tự động sinh (VD: KH-2026-XXXX)" class="w-full bg-gray-100 border-gray-200 rounded-lg text-gray-500 text-sm cursor-not-allowed shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Tên khóa học <span class="text-red-500">*</span></label>
                                    <input v-model="form.name" type="text" placeholder="Nhập tên khóa học..." required class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Đối tượng / Phạm vi đào tạo <span class="text-red-500">*</span></label>
                                    
                                    <select v-model="form.target_audience" required :disabled="sourceType === 'request'" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed">
                                        <template v-if="sourceType === 'request'">
                                            <option :value="form.target_audience">{{ form.target_audience }} (Kế thừa từ Yêu cầu)</option>
                                        </template>
                                        
                                        <template v-else>
                                            <option value="Toàn công ty">Toàn công ty</option>
                                            <option value="Cấp quản lý">Cấp quản lý (Toàn công ty)</option>
                                            <option value="Nhân viên mới">Nhân viên mới (Toàn công ty)</option>
                                        </template>
                                    </select>
                                    
                                    <div v-if="sourceType === 'request'" class="text-xs text-blue-600 mt-1 font-medium">Trường này đã bị khóa để đảm bảo đúng yêu cầu của Trưởng phòng.</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Thời lượng dự kiến (Giờ) <span class="text-red-500">*</span></label>
                                    <input v-model="form.duration" type="number" min="1" placeholder="VD: 8" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Hình thức đào tạo <span class="text-red-500">*</span></label>
                                    <select v-model="form.format" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                        <option value="Offline">Offline (Tại lớp)</option>
                                        <option value="Online">Online (Zoom/Teams)</option>
                                        <option value="Hybrid">Hybrid (Kết hợp)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Giảng viên dự kiến</label>
                                    <input v-model="form.instructor" type="text" placeholder="Nhập tên giảng viên..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Ghi chú thêm</label>
                                    <input v-model="form.notes" type="text" placeholder="Các thông tin cần lưu ý thêm..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                </div>
                            </div>
                        </div>

                        <div class="mb-10">
                            <div class="flex justify-between items-end mb-6">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">3. Chương trình học (Bài giảng)</h3>
                                <button type="button" @click="addLesson" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-sm font-bold transition-colors">
                                    <PlusCircleIcon class="w-5 h-5" /> Thêm Bài giảng
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div v-for="(lesson, index) in form.lessons" :key="index" class="p-5 bg-gray-50 border border-gray-200 rounded-xl relative group shadow-sm transition hover:border-blue-200">
                                    <div class="flex flex-col md:flex-row gap-4">
                                        <div class="w-8 h-8 rounded-full bg-white border border-gray-300 flex items-center justify-center font-bold text-gray-700 shrink-0 shadow-sm">
                                            {{ index + 1 }}
                                        </div>
                                        <div class="flex-1 grid grid-cols-1 md:grid-cols-12 gap-4">
                                            <div class="md:col-span-5">
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tên bài giảng <span class="text-red-500">*</span></label>
                                                <input v-model="lesson.title" type="text" placeholder="VD: Bài 1: Nhập môn..." required class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                            </div>
                                            
                                            <div class="md:col-span-3">
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Định dạng <span class="text-red-500">*</span></label>
                                                <select v-model="lesson.media_type" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                                    <option value="youtube">Video YouTube</option>
                                                    <option value="video_upload">Video Tải lên (MP4)</option>
                                                    <option value="slide_pdf">Tài liệu Slide (PDF)</option>
                                                </select>
                                            </div>

                                            <div class="md:col-span-3">
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Đường dẫn / File <span class="text-red-500">*</span></label>
                                                <input v-if="lesson.media_type === 'youtube'" v-model="lesson.media_url" type="url" required placeholder="https://youtube.com/..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                                <input v-else type="file" required @change="e => lesson.file = e.target.files[0]" class="w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer bg-white border border-gray-300 rounded-lg p-0.5">
                                            </div>

                                            <div class="md:col-span-1">
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Phút</label>
                                                <input v-model="lesson.duration" type="number" min="0" placeholder="0" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm text-center">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeLesson(index)" class="absolute -right-2 -top-2 w-7 h-7 bg-white border border-gray-200 text-gray-400 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all shadow-sm z-10" title="Xóa bài này">
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-10">
                            <div class="flex justify-between items-end mb-6">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">4. Bài kiểm tra / Đánh giá</h3>
                                <button type="button" @click="addAssignment" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg text-sm font-bold transition-colors">
                                    <PlusCircleIcon class="w-5 h-5" /> Thêm Bài kiểm tra
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div v-if="form.assignments.length === 0" class="p-6 bg-gray-50 border border-gray-200 border-dashed rounded-xl text-center text-sm text-gray-500 italic">
                                    Khóa học này chưa có bài kiểm tra nào. Bấm "Thêm Bài kiểm tra" để tạo.
                                </div>
                                
                                <div v-for="(assignment, index) in form.assignments" :key="index" class="p-5 bg-indigo-50/30 border border-indigo-100 rounded-xl relative group shadow-sm transition hover:border-indigo-300">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                                        <div class="md:col-span-6">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tên bài kiểm tra <span class="text-red-500">*</span></label>
                                            <input v-model="assignment.title" type="text" placeholder="VD: Bài test cuối khóa..." required class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Loại bài <span class="text-red-500">*</span></label>
                                            <select v-model="assignment.type" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                                <option value="mid_term">Giữa khóa</option>
                                                <option value="final">Cuối khóa</option>
                                                <option value="practice">Thực hành</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Điểm đạt (Thang 100)</label>
                                            <input v-model="assignment.pass_score" type="number" min="0" max="100" class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-center">
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Danh sách Câu hỏi / Yêu cầu <span class="text-red-500">*</span></label>
                                        
                                        <div v-for="(question, qIndex) in assignment.questions" :key="qIndex" class="flex gap-3 mb-3">
                                            <span class="font-bold text-sm text-gray-700 mt-2 whitespace-nowrap">Câu {{ qIndex + 1 }}:</span>
                                            <textarea v-model="assignment.questions[qIndex]" rows="2" required placeholder="Nhập nội dung câu hỏi..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm resize-none"></textarea>
                                            
                                            <button v-if="assignment.questions.length > 1" type="button" @click="assignment.questions.splice(qIndex, 1)" class="text-red-400 hover:text-red-600 px-2">
                                                <XMarkIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                        
                                        <button type="button" @click="assignment.questions.push('')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 mt-1 flex items-center gap-1">
                                            + Thêm câu hỏi
                                        </button>
                                    </div>
                                    
                                    <button type="button" @click="removeAssignment(index)" class="absolute -right-2 -top-2 w-7 h-7 bg-white border border-gray-200 text-gray-400 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all shadow-sm z-10" title="Xóa bài này">
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">5. Mô tả & Tài liệu đính kèm</h3>
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Mô tả hiển thị cho Học viên</label>
                                    <textarea v-model="form.description" rows="3" placeholder="Giới thiệu hấp dẫn về khóa học..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm resize-none"></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Đính kèm tài liệu (Sách, File bài tập)</label>
                                    
                                    <div class="flex gap-4 mb-4">
                                        <input type="file" ref="fileInput" @change="handleFileChange" class="hidden" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.zip,.rar">
                                        <button type="button" @click="triggerFileInput" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                                            <DocumentArrowUpIcon class="w-4 h-4 text-blue-600" /> Upload File
                                        </button>
                                        <button type="button" @click="showLinkInput = !showLinkInput" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                                            <LinkIcon class="w-4 h-4 text-blue-600" /> Thêm Link (Drive/Docs)
                                        </button>
                                    </div>

                                    <div v-if="showLinkInput" class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4 flex gap-3 items-end">
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Tên tài liệu (Link)</label>
                                            <input v-model="newLink.title" type="text" placeholder="VD: File bài tập Google Drive" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">URL (Đường dẫn) *</label>
                                            <input v-model="newLink.url" type="url" placeholder="https://..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                        </div>
                                        <button type="button" @click="addLink" class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-blue-700 transition-colors">Lưu Link</button>
                                    </div>

                                    <div v-if="form.documents.length > 0" class="space-y-2">
                                        <div v-for="(doc, index) in form.documents" :key="index" class="flex justify-between items-center bg-gray-50 px-4 py-3 rounded-lg border border-gray-200">
                                            <div class="flex items-center gap-3">
                                                <div class="p-1.5 bg-white rounded shadow-sm">
                                                    <DocumentArrowUpIcon v-if="doc.type === 'file'" class="w-5 h-5 text-gray-500" />
                                                    <LinkIcon v-else class="w-5 h-5 text-blue-500" />
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-800">{{ doc.title }}</p>
                                                    <p v-if="doc.type === 'link'" class="text-xs text-blue-500 truncate max-w-md">{{ doc.url }}</p>
                                                    <p v-if="doc.type === 'file'" class="text-xs text-gray-500">File đính kèm</p>
                                                </div>
                                            </div>
                                            <button type="button" @click="removeDocument(index)" class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors tooltip" title="Xóa tài liệu">
                                                <XMarkIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-100">
                            <Link :href="route('system.courses.index')" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-semibold transition-colors shadow-sm">
                                Hủy bỏ
                            </Link>
                            <button type="submit" :disabled="form.processing" class="px-8 py-2.5 bg-blue-600 text-white hover:bg-blue-700 rounded-lg font-semibold transition-colors shadow-sm shadow-blue-600/30 disabled:opacity-50 flex items-center gap-2">
                                <span v-if="form.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                                Hoàn tất Tạo khóa học
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>