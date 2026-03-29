<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ClockIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    classInfo: Object,
    quiz: Object,
    attempt: Object
});

const form = useForm({
    answers: {} // { question_id: [option_ids] }
});

// Khởi tạo form data rỗng cho mỗi câu hỏi
props.quiz.questions.forEach(q => {
    form.answers[q.id] = q.type === 'multiple' ? [] : null;
});

// LOGIC ĐẾM NGƯỢC
const timeLeft = ref(0);
let timerInterval = null;

const calculateTimeLeft = () => {
    const startedAt = new Date(props.attempt.started_at).getTime();
    const durationMs = props.quiz.duration_minutes * 60 * 1000;
    const endTime = startedAt + durationMs;
    const now = new Date().getTime();
    const remaining = Math.floor((endTime - now) / 1000);
    return remaining > 0 ? remaining : 0;
};

const formatTime = (seconds) => {
    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
    const s = (seconds % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
};

const submitQuiz = () => {
    form.post(route('employee.my-classes.quizzes.submit', [props.classInfo.id, props.quiz.id]));
};

onMounted(() => {
    timeLeft.value = calculateTimeLeft();
    if (timeLeft.value <= 0) {
        submitQuiz(); // Nếu vừa vào đã hết giờ thì auto nộp
        return;
    }
    
    timerInterval = setInterval(() => {
        timeLeft.value--;
        if (timeLeft.value <= 0) {
            clearInterval(timerInterval);
            alert("Đã hết thời gian làm bài! Hệ thống đang tự động nộp bài của bạn.");
            submitQuiz();
        }
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
    <Head :title="'Đang làm bài: ' + quiz.title" />

    <div class="min-h-screen bg-gray-50 flex flex-col">
        <header class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div>
                    <h1 class="font-bold text-gray-900 truncate max-w-xs sm:max-w-md">{{ quiz.title }}</h1>
                </div>
                <div class="flex items-center gap-2 bg-indigo-50 border border-indigo-200 px-4 py-1.5 rounded-full text-indigo-700 font-black" :class="{'bg-red-50 border-red-200 text-red-600': timeLeft < 60}">
                    <ClockIcon class="w-5 h-5" />
                    <span class="text-lg">{{ formatTime(timeLeft) }}</span>
                </div>
            </div>
        </header>

        <main class="flex-1 py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submitQuiz">
                    
                    <div class="space-y-8">
                        <div v-for="(q, index) in quiz.questions" :key="q.id" class="bg-white shadow-sm border border-gray-200 rounded-xl p-6">
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-10 h-10 shrink-0 bg-gray-100 rounded-full flex items-center justify-center font-bold text-gray-600">
                                    {{ index + 1 }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg leading-snug">{{ q.question_text }}</p>
                                    <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-bold">
                                        {{ q.type === 'single' ? 'Chọn 1 đáp án' : 'Chọn nhiều đáp án' }} • {{ q.points }} Điểm
                                    </p>
                                </div>
                            </div>

                            <div class="pl-14 space-y-3">
                                <label v-for="opt in q.options" :key="opt.id" class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-indigo-50 transition" :class="{'bg-indigo-50 border-indigo-300': (q.type==='single' ? form.answers[q.id] === opt.id : form.answers[q.id].includes(opt.id))}">
                                    <input v-if="q.type === 'single'" type="radio" :name="'q_' + q.id" :value="opt.id" v-model="form.answers[q.id]" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                    <input v-else type="checkbox" :value="opt.id" v-model="form.answers[q.id]" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 border-gray-300">
                                    <span class="ml-3 text-sm text-gray-700">{{ opt.option_text }}</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 bg-white shadow-sm border border-gray-200 rounded-xl p-6 flex items-center justify-between sticky bottom-4 z-40">
                        <p class="text-sm text-gray-500 font-medium">Hãy kiểm tra kỹ lại đáp án trước khi nộp.</p>
                        <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-indigo-600 text-white font-black rounded-lg hover:bg-indigo-700 shadow-md transition disabled:opacity-50">
                            {{ form.processing ? 'Đang nộp...' : 'NỘP BÀI' }}
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>