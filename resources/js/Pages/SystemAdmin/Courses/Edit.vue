<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeftIcon, DocumentArrowUpIcon, LinkIcon, XMarkIcon, DocumentTextIcon, PlusCircleIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    course: Object,
    departments: Array
});

// KHỞI TẠO DỮ LIỆU BÀI GIẢNG HIỆN TẠI (TỪ DATABASE)
const initialLessons = props.course.lessons ? props.course.lessons.map(lesson => ({
    id: lesson.id,
    title: lesson.title,
    media_type: lesson.media_type,
    media_url: lesson.media_url,
    duration: lesson.duration,
    is_existing: true, 
    file: null 
})) : [];

// KHỞI TẠO DỮ LIỆU BÀI KIỂM TRA HIỆN TẠI VỚI JSON CÂU HỎI
const initialAssignments = props.course.assignments ? props.course.assignments.map(ass => {
    let parsedQuestions = [''];
    try {
        parsedQuestions = typeof ass.content === 'string' ? JSON.parse(ass.content) : ass.content;
        if (!Array.isArray(parsedQuestions)) parsedQuestions = [ass.content];
    } catch(e) {
        parsedQuestions = [ass.content];
    }
    return {
        id: ass.id,
        title: ass.title,
        type: ass.type,
        questions: parsedQuestions, 
        pass_score: ass.pass_score,
        is_existing: true
    };
}) : [];

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
    
    lessons: initialLessons,    
    deleted_lesson_ids: [],     

    assignments: initialAssignments,
    deleted_assignment_ids: [],

    new_documents: [],          
    deleted_document_ids: []   
});

// ==========================================
// HÀM QUẢN LÝ BÀI GIẢNG (LESSONS)
// ==========================================
const addLesson = () => {
    form.lessons.push({ 
        title: '', 
        media_type: 'youtube', 
        media_url: '', 
        duration: '',
        is_existing: false, 
        file: null 
    });
};

const removeLesson = (index) => {
    if (form.lessons.length <= 1) {
        return alert('Phải có ít nhất 1 bài giảng trong khóa học!');
    }
    
    const lessonToRemove = form.lessons[index];
    if(confirm('Bạn có chắc chắn muốn xóa bài giảng này?')) {
        if (lessonToRemove.is_existing && lessonToRemove.id) {
            form.deleted_lesson_ids.push(lessonToRemove.id);
        }
        form.lessons.splice(index, 1);
    }
};

// ==========================================
// HÀM QUẢN LÝ BÀI KIỂM TRA (ASSIGNMENTS)
// ==========================================
const addAssignment = () => {
    form.assignments.push({ 
        title: '', 
        type: 'final', 
        questions: [''], 
        pass_score: 50, 
        is_existing: false 
    });
};

const removeAssignment = (index) => {
    const assToRemove = form.assignments[index];
    if(confirm('Xóa bài kiểm tra này khỏi khóa học?')) {
        if (assToRemove.is_existing && assToRemove.id) {
            form.deleted_assignment_ids.push(assToRemove.id);
        }
        form.assignments.splice(index, 1);
    }
};

// ==========================================
// QUẢN LÝ TÀI LIỆU (DOCUMENTS)
// ==========================================
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
    for (let i = 0; i < files.length; i++) {
        form.new_documents.push({ type: 'file', title: files[i].name, file: files[i] });
    }
    e.target.value = null;
};

const addLink = () => {
    if (newLink.value.url) {
        form.new_documents.push({ 
            type: 'link', 
            title: newLink.value.title || newLink.value.url, 
            url: newLink.value.url 
        });
        newLink.value = { title: '', url: '' };
        showLinkInput.value = false;
    }
};

const removeNewDocument = (index) => {
    form.new_documents.splice(index, 1);
};

// ==========================================
// GỬI LÊN SERVER
// ==========================================
const submitForm = () => {
    form.post(route('system.courses.update', props.course.id), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="'Sửa khóa học: ' + course.code" />

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
                    
                    <div class="mb-8 flex justify-between items-end border-b border-gray-100 pb-5">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Cập nhật khóa học</h2>
                            <p class="text-sm text-gray-500 mt-1">Chỉnh sửa thông tin chương trình đào tạo hiện tại.</p>
                        </div>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-md font-bold border border-blue-200 shadow-sm">{{ course.code }}</span>
                    </div>

                    <form @submit.prevent="submitForm">
                        
                        <div class="mb-10 bg-gray-50/50 p-6 rounded-xl border border-gray-100">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">1. Nguồn tạo khóa học (Không thể thay đổi)</h3>
                            
                            <div v-if="course.reason" class="max-w-2xl">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Phòng đào tạo đề xuất (Lý do)</label>
                                <input v-model="form.reason" type="text" class="w-full bg-gray-100 border-gray-200 rounded-lg shadow-sm text-gray-600 text-sm cursor-not-allowed font-medium" disabled>
                            </div>
                            
                            <div v-else class="max-w-2xl">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nguồn từ</label>
                                <input type="text" value="Yêu cầu đào tạo từ phòng ban" class="w-full bg-gray-100 border-gray-200 rounded-lg shadow-sm text-gray-600 text-sm cursor-not-allowed font-medium" disabled>
                            </div>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">2. Thông tin chi tiết</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Mã khóa học</label>
                                    <input type="text" disabled :value="course.code" class="w-full bg-gray-100 border-gray-200 rounded-lg text-gray-600 text-sm cursor-not-allowed shadow-sm font-bold">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Tên khóa học <span class="text-red-500">*</span></label>
                                    <input v-model="form.name" type="text" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm transition-colors">
                                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Đối tượng / Phạm vi đào tạo <span class="text-red-500">*</span></label>
                                    <select v-model="form.target_audience" required :disabled="!course.reason" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm transition-colors disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed">
                                        <template v-if="!course.reason">
                                            <option :value="form.target_audience">{{ form.target_audience }} (Kế thừa từ Yêu cầu)</option>
                                        </template>
                                        
                                        <template v-else>
                                            <option value="Toàn công ty">Toàn công ty</option>
                                            <option value="Cấp quản lý">Cấp quản lý (Toàn công ty)</option>
                                            <option value="Nhân viên mới">Nhân viên mới (Toàn công ty)</option>
                                        </template>
                                    </select>
                                    <div v-if="!course.reason" class="text-xs text-blue-600 mt-1 font-medium">Trường này đã bị khóa để đảm bảo đúng yêu cầu của Trưởng phòng.</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Thời lượng dự kiến (Giờ) <span class="text-red-500">*</span></label>
                                    <input v-model="form.duration" type="number" min="1" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm transition-colors">
                                    <div v-if="form.errors.duration" class="text-red-500 text-xs mt-1">{{ form.errors.duration }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Hình thức đào tạo <span class="text-red-500">*</span></label>
                                    <select v-model="form.format" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm transition-colors">
                                        <option value="Offline">Offline (Tại lớp)</option>
                                        <option value="Online">Online (Zoom/Teams)</option>
                                        <option value="Hybrid">Hybrid (Kết hợp)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Giảng viên dự kiến</label>
                                    <input v-model="form.instructor" type="text" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm transition-colors">
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Ghi chú thêm</label>
                                    <input v-model="form.notes" type="text" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm transition-colors">
                                </div>
                            </div>
                        </div>

                        <div class="mb-10">
                            <div class="flex justify-between items-end mb-6">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">3. Chương trình học (Bài giảng)</h3>
                                <button type="button" @click="addLesson" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg text-sm font-bold transition-colors border border-amber-200">
                                    <PlusCircleIcon class="w-5 h-5" /> Thêm Bài giảng
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div v-for="(lesson, index) in form.lessons" :key="index" :class="['p-5 border rounded-xl relative group shadow-sm transition hover:border-amber-300', lesson.is_existing ? 'bg-white border-gray-200' : 'bg-amber-50/30 border-amber-100']">
                                    <div class="flex flex-col md:flex-row gap-4">
                                        <div class="w-8 h-8 rounded-full bg-white border border-gray-300 flex items-center justify-center font-bold text-gray-700 shrink-0 shadow-sm">
                                            {{ index + 1 }}
                                        </div>
                                        <div class="flex-1 grid grid-cols-1 md:grid-cols-12 gap-4">
                                            <div class="md:col-span-5">
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tên bài giảng <span class="text-red-500">*</span></label>
                                                <input v-model="lesson.title" type="text" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                            </div>
                                            
                                            <div class="md:col-span-3">
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Định dạng <span class="text-red-500">*</span></label>
                                                <select v-model="lesson.media_type" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                                    <option value="youtube">Video YouTube</option>
                                                    <option value="video_upload">Tải lên Video MP4</option>
                                                    <option value="slide_pdf">Tài liệu Slide (PDF)</option>
                                                </select>
                                            </div>

                                            <div class="md:col-span-3">
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Đường dẫn / File <span class="text-red-500">*</span></label>
                                                
                                                <div v-if="lesson.is_existing && lesson.media_type !== 'youtube' && !lesson.file" class="flex items-center justify-between bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5">
                                                    <span class="text-xs text-gray-500 truncate" title="Đã có file trên hệ thống">Đã tải lên tệp đính kèm</span>
                                                    <button type="button" @click="lesson.file = 'replace'" class="text-xs font-bold text-blue-600 hover:underline">Đổi tệp</button>
                                                </div>
                                                
                                                <input v-else-if="lesson.media_type === 'youtube'" v-model="lesson.media_url" type="url" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                                
                                                <input v-else type="file" @change="e => lesson.file = e.target.files[0]" class="w-full text-sm text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer bg-white border border-gray-300 rounded-lg p-0.5">
                                            </div>

                                            <div class="md:col-span-1">
                                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Phút</label>
                                                <input v-model="lesson.duration" type="number" min="0" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm text-center">
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
                                <button type="button" @click="addAssignment" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 rounded-lg text-sm font-bold transition-colors">
                                    <PlusCircleIcon class="w-5 h-5" /> Thêm Bài kiểm tra
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div v-if="form.assignments.length === 0" class="p-6 bg-gray-50 border border-gray-200 border-dashed rounded-xl text-center text-sm text-gray-500 italic">
                                    Khóa học này chưa có bài kiểm tra nào. Bấm "Thêm Bài kiểm tra" để tạo.
                                </div>
                                
                                <div v-for="(assignment, index) in form.assignments" :key="index" :class="['p-5 border rounded-xl relative group shadow-sm transition hover:border-amber-300', assignment.is_existing ? 'bg-white border-gray-200' : 'bg-amber-50/30 border-amber-100']">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                                        <div class="md:col-span-6">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tên bài kiểm tra <span class="text-red-500">*</span></label>
                                            <input v-model="assignment.title" type="text" placeholder="VD: Bài test cuối khóa..." required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Loại bài <span class="text-red-500">*</span></label>
                                            <select v-model="assignment.type" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                                <option value="mid_term">Giữa khóa</option>
                                                <option value="final">Cuối khóa</option>
                                                <option value="practice">Thực hành</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Điểm đạt (Thang 100)</label>
                                            <input v-model="assignment.pass_score" type="number" min="0" max="100" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm text-center">
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Danh sách Câu hỏi / Yêu cầu <span class="text-red-500">*</span></label>
                                        
                                        <div v-for="(question, qIndex) in assignment.questions" :key="qIndex" class="flex gap-3 mb-3">
                                            <span class="font-bold text-sm text-gray-700 mt-2 whitespace-nowrap">Câu {{ qIndex + 1 }}:</span>
                                            <textarea v-model="assignment.questions[qIndex]" rows="2" required placeholder="Nhập nội dung câu hỏi..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm resize-none"></textarea>
                                            
                                            <button v-if="assignment.questions.length > 1" type="button" @click="assignment.questions.splice(qIndex, 1)" class="text-red-400 hover:text-red-600 px-2">
                                                <XMarkIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                        
                                        <button type="button" @click="assignment.questions.push('')" class="text-xs font-bold text-amber-600 hover:text-amber-800 mt-1 flex items-center gap-1">
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
                                    <textarea v-model="form.description" rows="3" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm resize-none transition-colors"></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Đính kèm tài liệu mới</label>
                                    <div class="flex gap-4 mb-4">
                                        <input type="file" ref="fileInput" @change="handleFileChange" class="hidden" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.zip,.rar">
                                        <button type="button" @click="triggerFileInput" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                                            <DocumentArrowUpIcon class="w-4 h-4 text-amber-600" /> Upload File
                                        </button>
                                        <button type="button" @click="showLinkInput = !showLinkInput" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                                            <LinkIcon class="w-4 h-4 text-amber-600" /> Thêm Link (Drive/Docs)
                                        </button>
                                    </div>

                                    <div v-if="showLinkInput" class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4 flex gap-3 items-end">
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Tên tài liệu (Link)</label>
                                            <input v-model="newLink.title" type="text" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-xs font-medium text-gray-500 mb-1">URL (Đường dẫn) *</label>
                                            <input v-model="newLink.url" type="url" class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                        </div>
                                        <button type="button" @click="addLink" class="px-5 py-2 bg-amber-500 text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-amber-600 transition-colors">Lưu Link</button>
                                    </div>

                                    <div class="space-y-3 mt-6">
                                        <div v-if="existingDocuments.length > 0" class="mb-4">
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Đang có trên hệ thống:</p>
                                            <div class="space-y-2">
                                                <div v-for="(doc, index) in existingDocuments" :key="doc.id" class="flex justify-between items-center bg-gray-50 px-4 py-3 rounded-lg border border-gray-200">
                                                    <div class="flex items-center gap-3">
                                                        <div class="p-1.5 bg-white rounded shadow-sm">
                                                            <DocumentTextIcon v-if="doc.type === 'file'" class="w-5 h-5 text-gray-500" />
                                                            <LinkIcon v-else class="w-5 h-5 text-blue-500" />
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-semibold text-gray-800">{{ doc.title }}</p>
                                                            <p class="text-xs text-gray-500">{{ doc.type === 'file' ? 'File đính kèm gốc' : 'Link tài liệu' }}</p>
                                                        </div>
                                                    </div>
                                                    <button type="button" @click="removeExistingDocument(doc.id, index)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Xóa tài liệu này">
                                                        <XMarkIcon class="w-5 h-5" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="form.new_documents.length > 0">
                                            <p class="text-xs font-bold text-green-500 uppercase tracking-wider mb-2">Tài liệu mới chuẩn bị thêm:</p>
                                            <div class="space-y-2">
                                                <div v-for="(doc, index) in form.new_documents" :key="index" class="flex justify-between items-center bg-green-50/50 px-4 py-3 rounded-lg border border-green-100">
                                                    <div class="flex items-center gap-3">
                                                        <div class="p-1.5 bg-white rounded shadow-sm">
                                                            <DocumentArrowUpIcon v-if="doc.type === 'file'" class="w-5 h-5 text-green-500" />
                                                            <LinkIcon v-else class="w-5 h-5 text-green-500" />
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-semibold text-green-800">{{ doc.title }}</p>
                                                            <p class="text-xs text-green-600 truncate max-w-md">{{ doc.type === 'file' ? 'File mới tải lên' : doc.url }}</p>
                                                        </div>
                                                    </div>
                                                    <button type="button" @click="removeNewDocument(index)" class="p-1.5 text-green-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Hủy thêm tài liệu">
                                                        <XMarkIcon class="w-5 h-5" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-100">
                            <Link :href="route('system.courses.index')" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-semibold transition-colors shadow-sm">
                                Hủy bỏ
                            </Link>
                            <button type="submit" :disabled="form.processing" class="px-8 py-2.5 bg-amber-500 text-white hover:bg-amber-600 rounded-lg font-semibold transition-colors shadow-sm shadow-amber-500/30 disabled:opacity-50 flex items-center gap-2">
                                <span v-if="form.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                                Lưu thông tin Cập nhật
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>