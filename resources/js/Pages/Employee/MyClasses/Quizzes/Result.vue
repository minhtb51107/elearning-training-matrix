<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { CheckCircleIcon, XCircleIcon, ArrowLeftIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    classInfo: Object,
    quiz: Object,
    attempt: Object
});

const isPassed = props.attempt.status === 'passed';

// Hàm check xem option đó user có chọn không
const isSelected = (questionId, optionId) => {
    return props.attempt.answers.some(a => a.quiz_question_id === questionId && a.quiz_option_id === optionId);
};
</script>

<template>
    <Head :title="'Kết quả: ' + quiz.title" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-6">
                    <Link :href="route('employee.my-classes.show', classInfo.id)" class="flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-indigo-600 transition">
                        <ArrowLeftIcon class="w-4 h-4" /> Quay lại lớp học
                    </Link>
                </div>

                <div class="bg-white shadow-xl sm:rounded-2xl p-8 text-center border-t-8 mb-8" :class="isPassed ? 'border-green-500' : 'border-red-500'">
                    <component :is="isPassed ? CheckCircleIcon : XCircleIcon" class="w-20 h-20 mx-auto mb-4" :class="isPassed ? 'text-green-500' : 'text-red-500'" />
                    
                    <h1 class="text-3xl font-black text-gray-900 mb-2">{{ isPassed ? 'CHÚC MỪNG BẠN ĐÃ ĐẠT!' : 'RẤT TIẾC, BẠN CHƯA ĐẠT!' }}</h1>
                    <p class="text-gray-500 mb-6">Bài kiểm tra: {{ quiz.title }}</p>

                    <div class="inline-block bg-gray-50 rounded-xl border border-gray-200 px-10 py-6 text-center">
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-2">ĐIỂM CỦA BẠN</p>
                        <p class="text-5xl font-black mb-1" :class="isPassed ? 'text-green-600' : 'text-red-600'">
                            {{ attempt.score }}<span class="text-2xl text-gray-400">/100</span>
                        </p>
                        <p class="text-xs font-bold text-gray-400">Điểm cần đạt: {{ quiz.pass_score }}</p>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-xl p-6 sm:p-8">
                    <h3 class="text-lg font-black text-gray-900 mb-6 border-b pb-4">Chi tiết bài làm của bạn</h3>
                    
                    <div class="space-y-6">
                        <div v-for="(q, index) in quiz.questions" :key="q.id" class="p-5 border border-gray-100 rounded-lg bg-gray-50">
                            <p class="font-bold text-gray-800 mb-4 leading-relaxed">Câu {{ index + 1 }}: {{ q.question_text }}</p>
                            
                            <div class="space-y-2">
                                <div v-for="opt in q.options" :key="opt.id" class="p-3 rounded-md text-sm border flex items-center gap-3"
                                    :class="{
                                        'bg-green-50 border-green-200 text-green-800': opt.is_correct && isSelected(q.id, opt.id), /* Chọn đúng */
                                        'bg-red-50 border-red-200 text-red-800': !opt.is_correct && isSelected(q.id, opt.id), /* Chọn sai */
                                        'bg-blue-50 border-blue-200 text-blue-800': opt.is_correct && !isSelected(q.id, opt.id), /* Bỏ sót đáp án đúng */
                                        'bg-white border-gray-200 text-gray-500': !opt.is_correct && !isSelected(q.id, opt.id) /* Không chọn và cũng sai */
                                    }">
                                    
                                    <span v-if="opt.is_correct && isSelected(q.id, opt.id)" class="text-green-600 font-black text-lg">✓</span>
                                    <span v-else-if="!opt.is_correct && isSelected(q.id, opt.id)" class="text-red-600 font-black text-lg">✗</span>
                                    <span v-else-if="opt.is_correct && !isSelected(q.id, opt.id)" class="text-blue-600 font-black text-lg">!</span>
                                    <span v-else class="text-gray-300 text-lg">○</span>
                                    
                                    <span :class="{'font-bold': isSelected(q.id, opt.id)}">{{ opt.option_text }}</span>
                                    
                                    <span v-if="isSelected(q.id, opt.id)" class="ml-auto text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-white/50">Bạn chọn</span>
                                    <span v-if="opt.is_correct && !isSelected(q.id, opt.id)" class="ml-auto text-[10px] uppercase font-bold tracking-wider text-blue-600 px-2 py-0.5 rounded bg-blue-100/50">Đáp án đúng</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>