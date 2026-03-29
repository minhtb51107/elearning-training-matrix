<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ChevronLeftIcon, DocumentArrowUpIcon, LinkIcon, XMarkIcon, DocumentTextIcon, PlusCircleIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    course: Object
});

// KHỞI TẠO DỮ LIỆU BÀI GIẢNG HIỆN TẠI
const initialLessons = props.course.lessons ? props.course.lessons.map(lesson => ({
    id: lesson.id,
    title: lesson.title,
    media_type: lesson.media_type,
    media_url: lesson.media_url,
    duration: lesson.duration,
    is_existing: true, 
    file: null 
})) : [];

// KHỞI TẠO DỮ LIỆU BÀI TỰ LUẬN HIỆN TẠI
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

// KHỞI TẠO DỮ LIỆU BÀI TRẮC NGHIỆM HIỆN TẠI
const initialQuizzes = props.course.quizzes ? props.course.quizzes.map(q => ({
    id: q.id,
    title: q.title,
    duration_minutes: q.duration_minutes,
    pass_score: q.pass_score,
    is_existing: true,
    questions: q.questions ? q.questions.map(qq => ({
        id: qq.id,
        question_text: qq.question_text,
        type: qq.type,
        points: qq.points,
        is_existing: true,
        options: qq.options ? qq.options.map(opt => ({
            id: opt.id,
            option_text: opt.option_text,
            is_correct: opt.is_correct == 1 || opt.is_correct === true,
            is_existing: true
        })) : []
    })) : []
})) : [];

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

    quizzes: initialQuizzes,
    deleted_quiz_ids: [],
    deleted_quiz_question_ids: [],

    new_documents: [],          
    deleted_document_ids: []   
});

// ==========================================
// HÀM QUẢN LÝ BÀI GIẢNG (LESSONS)
// ==========================================
const addLesson = () => {
    form.lessons.push({ title: '', media_type: 'youtube', media_url: '', duration: '', is_existing: false, file: null });
};

const removeLesson = (index) => {
    const lessonToRemove = form.lessons[index];
    if(confirm('Bạn có chắc chắn muốn xóa bài giảng này?')) {
        if (lessonToRemove.is_existing && lessonToRemove.id) form.deleted_lesson_ids.push(lessonToRemove.id);
        form.lessons.splice(index, 1);
    }
};

// ==========================================
// HÀM QUẢN LÝ BÀI TỰ LUẬN (ASSIGNMENTS)
// ==========================================
const addAssignment = () => {
    form.assignments.push({ title: '', type: 'final', questions: [''], pass_score: 50, is_existing: false });
};

const removeAssignment = (index) => {
    const assToRemove = form.assignments[index];
    if(confirm('Xóa bài tập tự luận này?')) {
        if (assToRemove.is_existing && assToRemove.id) form.deleted_assignment_ids.push(assToRemove.id);
        form.assignments.splice(index, 1);
    }
};

// ==========================================
// HÀM QUẢN LÝ BÀI TRẮC NGHIỆM (QUIZZES)
// ==========================================
const addQuiz = () => {
    form.quizzes.push({ 
        title: '', duration_minutes: 30, pass_score: 70, is_existing: false,
        questions: [{ 
            question_text: '', type: 'single', points: 10, is_existing: false,
            options: [{ option_text: '', is_correct: true, is_existing: false }, { option_text: '', is_correct: false, is_existing: false }] 
        }] 
    });
};

const removeQuiz = (index) => {
    const quizToRemove = form.quizzes[index];
    if(confirm('Xóa bài thi trắc nghiệm này?')) {
        if (quizToRemove.is_existing && quizToRemove.id) form.deleted_quiz_ids.push(quizToRemove.id);
        form.quizzes.splice(index, 1);
    }
};

const addQuizQuestion = (quizIndex) => {
    form.quizzes[quizIndex].questions.push({
        question_text: '', type: 'single', points: 10, is_existing: false,
        options: [{ option_text: '', is_correct: true, is_existing: false }, { option_text: '', is_correct: false, is_existing: false }]
    });
};

const removeQuizQuestion = (quizIndex, qIndex) => {
    const qToRemove = form.quizzes[quizIndex].questions[qIndex];
    if (qToRemove.is_existing && qToRemove.id) form.deleted_quiz_question_ids.push(qToRemove.id);
    form.quizzes[quizIndex].questions.splice(qIndex, 1);
};

const addQuizOption = (quizIndex, qIndex) => {
    form.quizzes[quizIndex].questions[qIndex].options.push({ option_text: '', is_correct: false, is_existing: false });
};

const removeQuizOption = (quizIndex, qIndex, optIndex) => {
    form.quizzes[quizIndex].questions[qIndex].options.splice(optIndex, 1);
};

const setCorrectOption = (quizIndex, qIndex, optIndex) => {
    const question = form.quizzes[quizIndex].questions[qIndex];
    if (question.type === 'single') {
        question.options.forEach((opt, idx) => { opt.is_correct = (idx === optIndex); });
    } else {
        question.options[optIndex].is_correct = !question.options[optIndex].is_correct;
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
        form.new_documents.push({ type: 'link', title: newLink.value.title || newLink.value.url, url: newLink.value.url });
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
    form.transform((data) => ({
        ...data,
        // Đảm bảo dữ liệu mảng lồng nhau không bị InertiaJS chuyển sai định dạng
        quizzes: Array.from(data.quizzes || []), 
        deleted_quiz_ids: Array.from(data.deleted_quiz_ids || []),
        deleted_quiz_question_ids: Array.from(data.deleted_quiz_question_ids || []),
    })).post(route('system.courses.update', props.course.id), { 
        preserveScroll: true 
    });
};
</script>

<template>
    <Head :title="'Chi tiết khóa học: ' + course.code" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('system.courses.index')" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                <ChevronLeftIcon class="w-4 h-4" /> Quay lại danh sách Khóa học
            </Link>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm font-bold">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm">
                    <span class="font-medium">{{ $page.props.flash.error }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 p-8 sm:p-10">
                    <div class="mb-8 flex justify-between items-end border-b border-gray-100 pb-5">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Chi tiết Khóa học & Giáo trình</h2>
                            <p class="text-sm text-gray-500 mt-1">Bạn có thể quản lý Bài giảng, Tự luận và Trắc nghiệm tại đây.</p>
                        </div>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-md font-bold border border-blue-200 shadow-sm">{{ course.code }}</span>
                    </div>

                    <form @submit.prevent="submitForm">
                        
                        <div class="mb-10 bg-gray-50/50 p-6 rounded-xl border border-gray-100">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">1. Thông tin chung</h3>
                                <Link :href="route('system.courses.edit', course.id)" class="text-sm text-blue-600 font-bold hover:underline">Sửa thông tin cơ bản</Link>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <p><span class="text-gray-500">Tên khóa:</span> <span class="font-bold">{{ form.name }}</span></p>
                                <p><span class="text-gray-500">Đối tượng:</span> <span class="font-bold">{{ form.target_audience }}</span></p>
                                <p><span class="text-gray-500">Thời lượng:</span> <span class="font-bold">{{ form.duration }} tiếng</span></p>
                                <p><span class="text-gray-500">Hình thức:</span> <span class="font-bold">{{ form.format }}</span></p>
                            </div>
                        </div>

                        <div class="mb-10">
                            <div class="flex justify-between items-end mb-6">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">2. Chương trình học (Bài giảng)</h3>
                                <button type="button" @click="addLesson" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg text-sm font-bold transition-colors border border-amber-200">
                                    <PlusCircleIcon class="w-5 h-5" /> Thêm Bài giảng
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div v-if="form.lessons.length === 0" class="p-6 bg-gray-50 border border-gray-200 border-dashed rounded-xl text-center text-sm text-gray-500 italic">Chưa có bài giảng nào.</div>

                                <div v-for="(lesson, index) in form.lessons" :key="index" :class="['p-5 border rounded-xl relative group shadow-sm transition hover:border-amber-300', lesson.is_existing ? 'bg-white border-gray-200' : 'bg-amber-50/30 border-amber-100']">
                                    <div class="flex flex-col md:flex-row gap-4">
                                        <div class="w-8 h-8 rounded-full bg-white border border-gray-300 flex items-center justify-center font-bold text-gray-700 shrink-0 shadow-sm">{{ index + 1 }}</div>
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
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">3. Bài tập Tự luận</h3>
                                <button type="button" @click="addAssignment" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-lg text-sm font-bold transition-colors">
                                    <PlusCircleIcon class="w-5 h-5" /> Thêm Tự luận
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div v-if="form.assignments.length === 0" class="p-6 bg-gray-50 border border-gray-200 border-dashed rounded-xl text-center text-sm text-gray-500 italic">
                                    Khóa học này chưa có bài tập tự luận nào.
                                </div>
                                
                                <div v-for="(assignment, index) in form.assignments" :key="index" :class="['p-5 border rounded-xl relative group shadow-sm transition hover:border-emerald-300', assignment.is_existing ? 'bg-white border-gray-200' : 'bg-emerald-50/30 border-emerald-100']">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                                        <div class="md:col-span-6">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tên bài tự luận <span class="text-red-500">*</span></label>
                                            <input v-model="assignment.title" type="text" placeholder="VD: Bài tập thực hành..." required class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500 shadow-sm">
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Loại bài <span class="text-red-500">*</span></label>
                                            <select v-model="assignment.type" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500 shadow-sm">
                                                <option value="mid_term">Giữa khóa</option>
                                                <option value="final">Cuối khóa</option>
                                                <option value="practice">Thực hành</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Điểm đạt (Thang 100)</label>
                                            <input v-model="assignment.pass_score" type="number" min="0" max="100" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500 shadow-sm text-center">
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Danh sách Câu hỏi / Yêu cầu <span class="text-red-500">*</span></label>
                                        
                                        <div v-for="(question, qIndex) in assignment.questions" :key="qIndex" class="flex gap-3 mb-3">
                                            <span class="font-bold text-sm text-gray-700 mt-2 whitespace-nowrap">Câu {{ qIndex + 1 }}:</span>
                                            <textarea v-model="assignment.questions[qIndex]" rows="2" required placeholder="Nhập nội dung yêu cầu..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500 shadow-sm resize-none"></textarea>
                                            
                                            <button v-if="assignment.questions.length > 1" type="button" @click="assignment.questions.splice(qIndex, 1)" class="text-red-400 hover:text-red-600 px-2">
                                                <XMarkIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                        
                                        <button type="button" @click="assignment.questions.push('')" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 mt-1 flex items-center gap-1">
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
                            <div class="flex justify-between items-end mb-6">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">4. Bài thi Trắc nghiệm (Quiz)</h3>
                                <button type="button" @click="addQuiz" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded-lg text-sm font-bold transition-colors">
                                    <PlusCircleIcon class="w-5 h-5" /> Thêm Đề Trắc Nghiệm
                                </button>
                            </div>
                            <div class="space-y-6">
                                <div v-if="form.quizzes.length === 0" class="p-6 bg-gray-50 border border-gray-200 border-dashed rounded-xl text-center text-sm text-gray-500 italic">Khóa học này chưa có bài thi trắc nghiệm.</div>
                                
                                <div v-for="(quiz, qzIdx) in form.quizzes" :key="qzIdx" :class="['p-5 border rounded-xl relative group/quiz shadow-sm transition', quiz.is_existing ? 'bg-white border-gray-200' : 'bg-indigo-50/30 border-indigo-100']">
                                    <button type="button" @click="removeQuiz(qzIdx)" class="absolute -right-2 -top-2 w-7 h-7 bg-white border border-gray-200 text-gray-400 rounded-full flex items-center justify-center opacity-0 group-hover/quiz:opacity-100 hover:bg-red-500 hover:text-white transition-all shadow-sm z-10"><XMarkIcon class="w-4 h-4" /></button>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 pb-4 border-b border-gray-200">
                                        <div class="md:col-span-6">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tên bài thi Trắc nghiệm <span class="text-red-500">*</span></label>
                                            <input v-model="quiz.title" type="text" required placeholder="VD: Thi trắc nghiệm cuối khóa..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 shadow-sm">
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Thời gian (Phút) <span class="text-red-500">*</span></label>
                                            <input v-model="quiz.duration_minutes" type="number" min="1" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 shadow-sm text-center">
                                        </div>
                                        <div class="md:col-span-3">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Điểm đạt (Thang 100) <span class="text-red-500">*</span></label>
                                            <input v-model="quiz.pass_score" type="number" min="1" max="100" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 shadow-sm text-center">
                                        </div>
                                    </div>

                                    <div class="mt-2 pl-2 border-l-2 border-indigo-200">
                                        <div class="flex items-center justify-between mb-4">
                                            <label class="block text-sm font-bold text-indigo-800 uppercase">Danh sách Câu hỏi Trắc nghiệm</label>
                                            <button type="button" @click="addQuizQuestion(qzIdx)" class="text-xs font-bold bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded hover:bg-indigo-200 transition">+ Thêm Câu</button>
                                        </div>

                                        <div class="space-y-4">
                                            <div v-for="(question, qstIdx) in quiz.questions" :key="qstIdx" class="bg-gray-50 p-4 rounded-lg border border-gray-200 relative group/qst">
                                                <button type="button" v-if="quiz.questions.length > 1" @click="removeQuizQuestion(qzIdx, qstIdx)" class="absolute right-2 top-2 text-red-400 hover:text-red-600 opacity-0 group-hover/qst:opacity-100 transition"><XMarkIcon class="w-5 h-5" /></button>
                                                
                                                <div class="grid grid-cols-12 gap-3 mb-3">
                                                    <div class="col-span-1 text-center font-bold text-gray-500 mt-2">C.{{ qstIdx + 1 }}</div>
                                                    <div class="col-span-7">
                                                        <textarea v-model="question.question_text" rows="2" required placeholder="Nhập câu hỏi..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 shadow-sm"></textarea>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <select v-model="question.type" class="w-full border-gray-300 rounded-lg text-xs focus:ring-indigo-500 shadow-sm">
                                                            <option value="single">1 Đáp án</option>
                                                            <option value="multiple">Nhiều đáp án</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <input v-model="question.points" type="number" min="1" placeholder="Điểm" required class="w-full border-gray-300 rounded-lg text-xs focus:ring-indigo-500 shadow-sm text-center">
                                                    </div>
                                                </div>

                                                <div class="pl-8 space-y-2">
                                                    <div v-for="(opt, optIdx) in question.options" :key="optIdx" class="flex items-center gap-2">
                                                        <input :type="question.type === 'single' ? 'radio' : 'checkbox'" :name="'quiz_'+qzIdx+'_q_'+qstIdx" :checked="opt.is_correct" @change="setCorrectOption(qzIdx, qstIdx, optIdx)" class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 cursor-pointer" title="Chọn làm đáp án đúng">
                                                        <input v-model="opt.option_text" type="text" required placeholder="Nội dung đáp án..." class="flex-1 border-gray-300 rounded text-sm focus:ring-indigo-500 shadow-sm" :class="opt.is_correct ? 'bg-green-50 border-green-300' : ''">
                                                        <button v-if="question.options.length > 2" type="button" @click="removeQuizOption(qzIdx, qstIdx, optIdx)" class="text-gray-400 hover:text-red-500"><XMarkIcon class="w-4 h-4" /></button>
                                                    </div>
                                                    <button type="button" @click="addQuizOption(qzIdx, qstIdx)" class="text-xs font-bold text-gray-500 hover:text-indigo-600 mt-1 ml-6">+ Thêm đáp án</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">5. Tài liệu đính kèm chung</h3>
                            <div class="space-y-6">
                                <div>
                                    <div class="flex gap-4 mb-4">
                                        <input type="file" ref="fileInput" @change="handleFileChange" class="hidden" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.png,.jpg,.jpeg,.zip,.rar">
                                        <button type="button" @click="triggerFileInput" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm"><DocumentArrowUpIcon class="w-4 h-4 text-blue-600" /> Upload File</button>
                                        <button type="button" @click="showLinkInput = !showLinkInput" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm"><LinkIcon class="w-4 h-4 text-blue-600" /> Thêm Link (Drive/Docs)</button>
                                    </div>

                                    <div v-if="showLinkInput" class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4 flex gap-3 items-end">
                                        <div class="flex-1"><label class="block text-xs font-medium text-gray-500 mb-1">Tên tài liệu</label><input v-model="newLink.title" type="text" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500"></div>
                                        <div class="flex-1"><label class="block text-xs font-medium text-gray-500 mb-1">URL *</label><input v-model="newLink.url" type="url" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500"></div>
                                        <button type="button" @click="addLink" class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold shadow-sm hover:bg-blue-700">Lưu Link</button>
                                    </div>

                                    <div class="space-y-3 mt-6">
                                        <div v-if="existingDocuments.length > 0" class="mb-4">
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

                                        <div v-if="form.new_documents.length > 0">
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
                            <Link :href="route('system.courses.index')" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-semibold shadow-sm transition-colors">Hủy bỏ</Link>
                            <button type="submit" :disabled="form.processing" class="px-8 py-2.5 bg-blue-600 text-white hover:bg-blue-700 rounded-lg font-semibold shadow-sm shadow-blue-600/30 disabled:opacity-50 flex items-center gap-2 transition-colors">
                                <span v-if="form.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>Lưu thông tin Cập nhật
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>