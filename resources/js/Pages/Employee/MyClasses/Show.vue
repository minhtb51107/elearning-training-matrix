<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import { 
    ChevronLeftIcon, 
    PlayCircleIcon, 
    CheckCircleIcon,
    DocumentTextIcon,
    VideoCameraIcon,
    ClipboardDocumentCheckIcon,
    ClipboardDocumentListIcon,
    ArrowUpTrayIcon,
    PaperClipIcon,
    ExclamationTriangleIcon // 👉 Thêm Icon Cảnh báo
} from '@heroicons/vue/20/solid';

const props = defineProps({
    classInfo: Object,
    course: Object,
    lessons: { type: Array, default: () => [] },
    documents: { type: Array, default: () => [] },
    assignments: { type: Array, default: () => [] },
    quizzes: { type: Array, default: () => [] }
});

const activeType = ref(props.lessons.length > 0 ? 'lesson' : (props.assignments.length > 0 ? 'assignment' : null));
const activeId = ref(activeType.value === 'lesson' ? props.lessons[0]?.id : props.assignments[0]?.id);

const activeLesson = computed(() => {
    if (activeType.value !== 'lesson') return null;
    return props.lessons.find(l => l.id === activeId.value) || null;
});

const activeAssignment = computed(() => {
    if (activeType.value !== 'assignment') return null;
    return props.assignments.find(a => a.id === activeId.value) || null;
});

// 👉 Tính toán xem học viên có được phép nộp Assignment không
const canSubmitAssignment = computed(() => {
    // Nếu khóa không có bài quiz nào, dĩ nhiên là cho nộp
    if (!props.quizzes || props.quizzes.length === 0) return true;
    
    // Nếu có quiz, bắt buộc tất cả quiz phải có status là 'passed'
    return props.quizzes.every(quiz => quiz.status === 'passed');
});

const submissionForm = useForm({
    assignment_id: activeType.value === 'assignment' ? activeId.value : '',
    answers: [], 
    file: null,
});

const setActive = (type, id) => {
    activeType.value = type;
    activeId.value = id;
    if (type === 'assignment') {
        submissionForm.assignment_id = id;
        submissionForm.answers = []; 
        submissionForm.clearErrors();
    }
};

const markLessonCompleted = (lessonId) => {
    const lesson = props.lessons.find(l => l.id === lessonId);
    if (!lesson || lesson.isCompleted) return;

    axios.post(route('employee.my-classes.complete-lesson', props.classInfo.id), {
        lesson_id: lessonId
    }).then(res => {
        if (res.data.success) {
            router.reload({ only: ['lessons', 'classInfo'] });
        }
    }).catch(err => console.error("Lỗi cập nhật tiến độ:", err));
};

const getYoutubeEmbedUrl = (url) => {
    if (!url) return '';
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    const videoId = (match && match[2].length === 11) ? match[2] : null;
    return videoId ? `https://www.youtube.com/embed/${videoId}?enablejsapi=1&rel=0` : '';
};

let ytPlayer = null;
const initYouTubeAPI = () => {
    if (activeLesson.value?.type === 'youtube') {
        if (window.YT && window.YT.Player) {
            ytPlayer = new window.YT.Player('yt-player-iframe', {
                events: {
                    'onStateChange': (event) => {
                        if (event.data === 0) markLessonCompleted(activeLesson.value.id);
                    }
                }
            });
        }
    }
};

watch(activeLesson, (newVal) => {
    if (newVal && newVal.type === 'youtube') {
        setTimeout(initYouTubeAPI, 1000); 
    }
});

onMounted(() => {
    if (!window.YT) {
        const tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        window.onYouTubeIframeAPIReady = initYouTubeAPI;
    } else {
        setTimeout(initYouTubeAPI, 500);
    }
});

const submitAssignment = () => {
    submissionForm.post(route('employee.submissions.store', props.classInfo.id), {
        preserveScroll: true,
        onSuccess: () => {
            submissionForm.reset('answers', 'file');
            router.reload({ only: ['assignments'] });
        }
    });
};
</script>

<template>
    <Head :title="course.name + ' - Đang học'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('employee.my-classes')" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                    <ChevronLeftIcon class="w-4 h-4" />
                    Quay lại Lớp học
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm font-bold flex items-center gap-2">
                    <CheckCircleIcon class="w-5 h-5" />
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm font-bold">
                    {{ $page.props.flash.error }}
                </div>

                <div class="flex flex-col lg:flex-row gap-6">
                    
                    <div class="flex-1 flex flex-col gap-6">
                        
                        <template v-if="activeType === 'lesson'">
                            <div class="bg-black rounded-xl overflow-hidden shadow-lg aspect-video relative flex items-center justify-center">
                                <template v-if="activeLesson">
                                    <iframe v-if="activeLesson.type === 'youtube'" 
                                            id="yt-player-iframe"
                                            class="w-full h-full" 
                                            :src="getYoutubeEmbedUrl(activeLesson.url)" 
                                            title="YouTube video player" 
                                            frameborder="0" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen>
                                    </iframe>
                                    
                                    <video v-else-if="activeLesson.type === 'video_upload'" 
                                           controls 
                                           class="w-full h-full"
                                           @ended="markLessonCompleted(activeLesson.id)">
                                        <source :src="activeLesson.url" type="video/mp4">
                                        Trình duyệt của bạn không hỗ trợ thẻ video.
                                    </video>
                                    
                                    <iframe v-else-if="activeLesson.type === 'slide_pdf'" 
                                            :src="activeLesson.url" 
                                            class="w-full h-full bg-white" 
                                            frameborder="0">
                                    </iframe>
                                </template>
                                
                                <div v-else class="text-gray-400">
                                    Chưa chọn bài học hoặc khóa học không có nội dung.
                                </div>
                            </div>

                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
                                <div class="flex items-start justify-between border-b border-gray-100 pb-6 mb-6">
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-md text-[11px] font-bold border border-blue-200 uppercase tracking-wider">
                                                {{ classInfo.code }}
                                            </span>
                                        </div>
                                        <h1 class="text-2xl font-bold text-gray-900">{{ activeLesson?.title || 'Tổng quan khóa học' }}</h1>
                                        <p class="text-sm text-gray-500 mt-2 font-medium">{{ course.name }}</p>
                                    </div>

                                    <button v-if="activeLesson && !activeLesson.isCompleted" 
                                            @click="markLessonCompleted(activeLesson.id)"
                                            class="px-5 py-2.5 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm font-bold hover:bg-green-100 flex items-center gap-2 transition cursor-pointer">
                                        <CheckCircleIcon class="w-5 h-5" /> Đánh dấu Hoàn thành
                                    </button>
                                    
                                    <div v-else-if="activeLesson && activeLesson.isCompleted" 
                                         class="px-5 py-2.5 bg-gray-50 text-gray-500 border border-gray-200 rounded-lg text-sm font-bold flex items-center gap-2">
                                        <CheckCircleIcon class="w-5 h-5 text-green-500" /> Đã hoàn thành
                                    </div>
                                </div>
                                
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 mb-3">Mô tả khóa học</h3>
                                    <p class="text-sm text-gray-600 leading-relaxed">{{ course.description || 'Chưa có thông tin mô tả chi tiết.' }}</p>
                                </div>
                            </div>
                        </template>

                        <div v-else-if="activeType === 'assignment' && activeAssignment" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
                            <div class="border-b border-gray-100 pb-6 mb-6">
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="bg-indigo-100 text-indigo-700 px-2.5 py-0.5 rounded-md text-[11px] font-bold border border-indigo-200 uppercase tracking-wider flex items-center gap-1">
                                        <ClipboardDocumentCheckIcon class="w-3 h-3" />
                                        {{ activeAssignment.type === 'mid_term' ? 'Giữa khóa' : (activeAssignment.type === 'final' ? 'Cuối khóa' : 'Thực hành') }}
                                    </span>
                                    <span class="bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-md text-[11px] font-bold border border-gray-200 uppercase tracking-wider">
                                        Điểm đạt: {{ activeAssignment.pass_score }}/100
                                    </span>
                                </div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ activeAssignment.title }}</h1>
                            </div>

                            <div v-if="activeAssignment.submission" class="p-6 rounded-xl border" :class="activeAssignment.submission.status === 'graded' ? (activeAssignment.submission.score >= activeAssignment.pass_score ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200') : 'bg-yellow-50 border-yellow-200'">
                                <h3 class="text-sm font-bold mb-4" :class="activeAssignment.submission.status === 'graded' ? (activeAssignment.submission.score >= activeAssignment.pass_score ? 'text-green-800' : 'text-red-800') : 'text-yellow-800'">
                                    TÌNH TRẠNG BÀI LÀM CỦA BẠN
                                </h3>
                                
                                <div v-if="activeAssignment.submission.status === 'pending'" class="text-sm text-yellow-700 font-medium flex items-center gap-2 mb-4">
                                    <span class="relative flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
                                    </span>
                                    Bài làm của bạn đã được gửi đi và đang chờ Giảng viên chấm điểm.
                                </div>

                                <div v-else>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Điểm số</p>
                                            <p class="text-3xl font-black" :class="activeAssignment.submission.score >= activeAssignment.pass_score ? 'text-green-600' : 'text-red-600'">
                                                {{ activeAssignment.submission.score }} <span class="text-sm text-gray-500 font-medium">/ 100</span>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Đánh giá</p>
                                            <p class="text-lg font-bold" :class="activeAssignment.submission.score >= activeAssignment.pass_score ? 'text-green-600' : 'text-red-600'">
                                                {{ activeAssignment.submission.score >= activeAssignment.pass_score ? 'ĐẠT' : 'KHÔNG ĐẠT' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div v-if="activeAssignment.submission.feedback" class="mt-4 pt-4 border-t" :class="activeAssignment.submission.score >= activeAssignment.pass_score ? 'border-green-200' : 'border-red-200'">
                                        <p class="text-xs text-gray-500 uppercase font-bold mb-2">Lời phê của Giảng viên</p>
                                        <p class="text-sm italic text-gray-800">"{{ activeAssignment.submission.feedback }}"</p>
                                    </div>
                                </div>

                                <div class="mt-6 pt-6 border-t" :class="activeAssignment.submission.status === 'graded' ? (activeAssignment.submission.score >= activeAssignment.pass_score ? 'border-green-200' : 'border-red-200') : 'border-yellow-200'">
                                    <h4 class="text-xs font-bold uppercase mb-4" :class="activeAssignment.submission.status === 'graded' ? (activeAssignment.submission.score >= activeAssignment.pass_score ? 'text-green-700' : 'text-red-700') : 'text-yellow-700'">Bài bạn đã nộp:</h4>
                                    
                                    <div class="space-y-4">
                                        <div v-for="(question, qIndex) in activeAssignment.questions" :key="qIndex">
                                            <p class="text-sm font-bold text-gray-700 mb-1">Câu {{ qIndex + 1 }}: <span class="font-normal">{{ question }}</span></p>
                                            <div class="p-3 bg-white/50 border border-gray-200/50 rounded-md text-sm text-gray-800 whitespace-pre-wrap">
                                                {{ activeAssignment.submission.answers ? activeAssignment.submission.answers[qIndex] : 'Chưa có câu trả lời text.' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="activeAssignment.submission.status === 'graded' && activeAssignment.submission.score < activeAssignment.pass_score" class="mt-6">
                                    <button @click="activeAssignment.submission = null" class="text-sm font-bold text-red-600 hover:text-red-700 underline">Làm lại bài</button>
                                </div>
                            </div>

                            <div v-else>
                                <h3 class="text-sm font-bold text-gray-900 mb-4 border-t border-gray-100 pt-6">Nộp bài làm của bạn</h3>
                                
                                <div v-if="submissionForm.errors.assignment_id" class="text-red-600 text-sm mb-3 font-bold bg-red-50 p-2 rounded">{{ submissionForm.errors.assignment_id }}</div>
                                <div v-if="submissionForm.errors.answers" class="text-red-600 text-sm mb-3 font-bold bg-red-50 p-2 rounded">{{ submissionForm.errors.answers }}</div>
                                <div v-if="submissionForm.errors.file" class="text-red-600 text-sm mb-3 font-bold bg-red-50 p-2 rounded">{{ submissionForm.errors.file }}</div>

                                <div v-if="!canSubmitAssignment" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-6 flex items-start gap-3">
                                    <ExclamationTriangleIcon class="w-6 h-6 text-yellow-600 shrink-0 mt-0.5" />
                                    <div>
                                        <h4 class="text-sm font-bold text-yellow-800">Cần hoàn thành Bài Trắc Nghiệm</h4>
                                        <p class="text-sm text-yellow-700 mt-1">Khóa học này yêu cầu bạn phải thi và đạt điểm <b>Tất cả các Bài thi Trắc nghiệm</b> trước khi được phép nộp bài Thực hành/Tự luận cuối khóa.</p>
                                    </div>
                                </div>

                                <form v-else @submit.prevent="submitAssignment">
                                    <div class="mb-5 space-y-6">
                                        <div v-for="(question, qIndex) in activeAssignment.questions" :key="qIndex">
                                            <label class="block text-sm font-bold text-gray-800 mb-2">Câu {{ qIndex + 1 }}: <span class="font-normal text-gray-600">{{ question }} <span class="text-red-500">*</span></span></label>
                                            <textarea v-model="submissionForm.answers[qIndex]" required rows="3" class="w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-colors" placeholder="Nhập câu trả lời của bạn tại đây..."></textarea>
                                        </div>
                                    </div>

                                    <div class="mb-6 border-t border-gray-100 pt-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Hoặc Upload File đính kèm (Word, Excel, PDF...)</label>
                                        <input type="file" @change="e => submissionForm.file = e.target.files[0]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-lg bg-gray-50 cursor-pointer">
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" :disabled="submissionForm.processing" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition shadow-sm flex items-center gap-2 disabled:opacity-50">
                                            <span v-if="submissionForm.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                                            <ArrowUpTrayIcon v-else class="w-5 h-5" />
                                            Nộp Bài Đánh Giá
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>

                    <div class="w-full lg:w-[380px] flex flex-col gap-6 shrink-0">
                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                            <h3 class="text-sm font-bold text-gray-900 mb-3">Tiến độ học tập</h3>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-gray-500 font-medium">Hoàn thành khóa học</span>
                                <span class="text-sm font-bold text-blue-600">{{ classInfo.progress || 0 }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full bg-blue-500 transition-all duration-500" :style="{ width: (classInfo.progress || 0) + '%' }"></div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col max-h-[700px]">
                            
                            <div class="p-4 bg-gray-50 border-b border-gray-200">
                                <h3 class="font-bold text-gray-900">Danh sách Bài giảng</h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ lessons.length }} bài học</p>
                            </div>
                            
                            <div class="overflow-y-auto max-h-[200px] p-2 space-y-1">
                                <div v-if="lessons.length === 0" class="p-4 text-center text-sm text-gray-500 italic">
                                    Khóa học này chưa có nội dung.
                                </div>
                                
                                <button v-for="(lesson, index) in lessons" :key="lesson.id" 
                                        @click="setActive('lesson', lesson.id)"
                                        :class="['w-full text-left p-3 rounded-lg flex gap-3 items-start transition-all', 
                                                activeType === 'lesson' && activeId === lesson.id ? 'bg-blue-50 border border-blue-200' : 'hover:bg-gray-50 border border-transparent']">
                                    
                                    <div class="mt-0.5 flex-shrink-0">
                                        <CheckCircleIcon v-if="lesson.isCompleted" class="w-5 h-5 text-green-500" />
                                        <template v-else>
                                            <PlayCircleIcon v-if="lesson.type.includes('video') || lesson.type === 'youtube'" 
                                                :class="['w-5 h-5', activeType === 'lesson' && activeId === lesson.id ? 'text-blue-600' : 'text-gray-400']" />
                                            <DocumentTextIcon v-else 
                                                :class="['w-5 h-5', activeType === 'lesson' && activeId === lesson.id ? 'text-blue-600' : 'text-gray-400']" />
                                        </template>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <p :class="['text-sm font-semibold line-clamp-2 leading-tight', activeType === 'lesson' && activeId === lesson.id ? 'text-blue-700' : 'text-gray-700']">
                                            {{ index + 1 }}. {{ lesson.title }}
                                        </p>
                                        <p class="text-xs mt-1" :class="activeType === 'lesson' && activeId === lesson.id ? 'text-blue-500' : 'text-gray-500'">
                                            {{ lesson.duration }} phút
                                        </p>
                                    </div>
                                </button>
                            </div>

                            <div class="p-4 bg-gray-50 border-y border-gray-200">
                                <h3 class="font-bold text-gray-900">Bài Tập Tự Luận</h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ assignments?.length || 0 }} bài tập</p>
                            </div>
                            
                            <div class="overflow-y-auto max-h-[150px] p-2 space-y-1">
                                <div v-if="!assignments || assignments.length === 0" class="p-4 text-center text-sm text-gray-500 italic">
                                    Không có bài tự luận nào.
                                </div>
                                
                                <button v-for="(assignment, index) in assignments" :key="assignment.id" 
                                        @click="setActive('assignment', assignment.id)"
                                        :class="['w-full text-left p-3 rounded-lg flex gap-3 items-start transition-all', 
                                                activeType === 'assignment' && activeId === assignment.id ? 'bg-indigo-50 border border-indigo-200' : 'hover:bg-gray-50 border border-transparent']">
                                    
                                    <div class="mt-0.5 flex-shrink-0">
                                        <CheckCircleIcon v-if="assignment.submission && assignment.submission.score >= assignment.pass_score" class="w-5 h-5 text-green-500" />
                                        <ClipboardDocumentCheckIcon v-else :class="['w-5 h-5', activeType === 'assignment' && activeId === assignment.id ? 'text-indigo-600' : 'text-gray-400']" />
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <p :class="['text-sm font-semibold line-clamp-2 leading-tight', activeType === 'assignment' && activeId === assignment.id ? 'text-indigo-700' : 'text-gray-700']">
                                            Bài {{ index + 1 }}: {{ assignment.title }}
                                        </p>
                                        <p class="text-[11px] mt-1 font-medium" :class="activeType === 'assignment' && activeId === assignment.id ? 'text-indigo-500' : 'text-gray-500'">
                                            {{ assignment.type === 'mid_term' ? 'Giữa khóa' : (assignment.type === 'final' ? 'Cuối khóa' : 'Thực hành') }}
                                        </p>
                                    </div>
                                </button>
                            </div>

                            <div class="p-4 bg-gray-50 border-y border-gray-200">
                                <h3 class="font-bold text-gray-900">Thi Trắc Nghiệm / Quiz</h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ quizzes?.length || 0 }} bài thi</p>
                            </div>
                            <div class="overflow-y-auto flex-1 p-2 space-y-1 bg-white">
                                <div v-if="!quizzes || quizzes.length === 0" class="p-4 text-center text-sm text-gray-500 italic">
                                    Không có bài thi trắc nghiệm nào.
                                </div>
                                
                                <Link v-for="(quiz, index) in quizzes" :key="quiz.id" 
                                        :href="route('employee.my-classes.quizzes.show', [classInfo.id, quiz.id])"
                                        class="w-full text-left p-3 rounded-lg flex gap-3 items-start transition-all hover:bg-gray-50 border border-transparent group">
                                    
                                    <div class="mt-0.5 flex-shrink-0">
                                        <CheckCircleIcon v-if="quiz.status === 'passed'" class="w-5 h-5 text-green-500" />
                                        <ClipboardDocumentListIcon v-else class="w-5 h-5 text-gray-400 group-hover:text-indigo-600" />
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold line-clamp-2 leading-tight text-gray-700 group-hover:text-indigo-700">
                                            {{ quiz.title }}
                                        </p>
                                        <p class="text-[11px] mt-1 font-medium text-gray-500">
                                            Thời gian: {{ quiz.duration_minutes }} phút • Điểm đạt: {{ quiz.pass_score }}
                                        </p>
                                    </div>
                                </Link>
                            </div>

                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-4 bg-gray-50 border-b border-gray-200">
                                <h3 class="font-bold text-gray-900 text-sm">Tài liệu tham khảo</h3>
                            </div>
                            <div class="p-2 space-y-1">
                                <div v-if="documents.length === 0" class="p-3 text-center text-xs text-gray-500 italic">
                                    Không có tài liệu đính kèm.
                                </div>
                                <a v-for="doc in documents" :key="doc.id" :href="doc.url" target="_blank"
                                   class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                                    <div class="p-1.5 bg-blue-50 text-blue-600 rounded">
                                        <DocumentTextIcon class="w-4 h-4" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-700 group-hover:text-blue-600 truncate">{{ doc.title }}</p>
                                    </div>
                                </a>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>