<script setup>
import { ref } from 'vue';
import { useForm, Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    course: Object,
    quiz: Object
});

const showQuestionForm = ref(false);

const questionForm = useForm({
    question_text: '',
    type: 'single',
    points: 10,
    options: [
        { option_text: '', is_correct: false },
        { option_text: '', is_correct: false },
    ]
});

const addOption = () => {
    questionForm.options.push({ option_text: '', is_correct: false });
};

const removeOption = (index) => {
    if (questionForm.options.length > 2) {
        questionForm.options.splice(index, 1);
    }
};

const submitQuestion = () => {
    questionForm.post(route('system.quizzes.questions.store', [props.course.id, props.quiz.id]), {
        preserveScroll: true,
        onSuccess: () => {
            showQuestionForm.value = false;
            questionForm.reset();
            questionForm.options = [
                { option_text: '', is_correct: false },
                { option_text: '', is_correct: false },
            ];
        }
    });
};

const deleteQuestion = (questionId) => {
    if (confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')) {
        router.delete(route('system.quizzes.questions.destroy', [props.course.id, props.quiz.id, questionId]), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head :title="'Quản lý câu hỏi: ' + quiz.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Bài thi: {{ quiz.title }} ({{ course.name }})
                </h2>
                <Link :href="route('system.courses.show', course.id)" class="text-sm text-gray-500 hover:underline">
                    &larr; Quay lại khóa học
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white shadow sm:rounded-lg p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Thời gian làm bài: <span class="font-bold text-gray-900">{{ quiz.duration_minutes }} phút</span></p>
                        <p class="text-sm text-gray-500 mt-1">Điểm đạt: <span class="font-bold text-green-600">{{ quiz.pass_score }}/100</span></p>
                        <p class="text-sm text-gray-500 mt-1">Tổng số câu hỏi hiện tại: <span class="font-bold text-blue-600">{{ quiz.questions.length }}</span></p>
                    </div>
                    <PrimaryButton @click="showQuestionForm = !showQuestionForm">
                        {{ showQuestionForm ? 'Hủy thêm câu hỏi' : '+ Thêm Câu Hỏi Mới' }}
                    </PrimaryButton>
                </div>

                <div v-if="showQuestionForm" class="bg-indigo-50 border border-indigo-200 shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-indigo-900 mb-4">Tạo câu hỏi mới</h3>
                    <form @submit.prevent="submitQuestion" class="space-y-4">
                        <div v-if="questionForm.errors.options" class="text-red-600 font-bold mb-2">{{ questionForm.errors.options }}</div>

                        <div>
                            <InputLabel value="Nội dung câu hỏi *" />
                            <textarea v-model="questionForm.question_text" required rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel value="Loại câu hỏi *" />
                                <select v-model="questionForm.type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="single">Chọn 1 đáp án đúng</option>
                                    <option value="multiple">Chọn nhiều đáp án đúng</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel value="Điểm cho câu này *" />
                                <TextInput type="number" v-model="questionForm.points" min="1" required class="mt-1 block w-full" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <InputLabel value="Các đáp án:" class="mb-2 font-bold" />
                            <div v-for="(opt, index) in questionForm.options" :key="index" class="flex items-center gap-3 mb-3">
                                <Checkbox v-model:checked="opt.is_correct" />
                                <TextInput v-model="opt.option_text" type="text" class="flex-1" :placeholder="'Nhập đáp án ' + (index + 1)" required />
                                <button type="button" @click="removeOption(index)" class="text-red-500 hover:text-red-700 text-sm font-bold" v-if="questionForm.options.length > 2">Xóa</button>
                            </div>
                            <button type="button" @click="addOption" class="text-indigo-600 hover:text-indigo-800 text-sm font-bold mt-2">+ Thêm đáp án</button>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-indigo-200">
                            <PrimaryButton :disabled="questionForm.processing">Lưu Câu Hỏi</PrimaryButton>
                        </div>
                    </form>
                </div>

                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 border-b pb-2">Danh sách câu hỏi trong bài</h3>
                    
                    <div v-if="quiz.questions.length === 0" class="text-center text-gray-500 py-4 italic">
                        Chưa có câu hỏi nào. Hãy thêm câu hỏi ở phía trên!
                    </div>

                    <div class="space-y-6">
                        <div v-for="(q, index) in quiz.questions" :key="q.id" class="p-4 border border-gray-200 rounded-lg bg-gray-50 relative">
                            <button @click="deleteQuestion(q.id)" class="absolute top-4 right-4 text-red-500 hover:text-red-700 font-bold text-sm">Xóa câu này</button>
                            
                            <p class="font-bold text-gray-800 mb-2">Câu {{ index + 1 }}: <span class="font-normal">{{ q.question_text }}</span></p>
                            <p class="text-xs text-gray-500 mb-3">Loại: {{ q.type === 'single' ? '1 Đáp án' : 'Nhiều đáp án' }} | Điểm: {{ q.points }}</p>
                            
                            <ul class="space-y-2">
                                <li v-for="(opt, oIndex) in q.options" :key="opt.id" class="flex items-start gap-2 text-sm">
                                    <span v-if="opt.is_correct" class="text-green-600 font-bold">✓</span>
                                    <span v-else class="text-gray-400">○</span>
                                    <span :class="opt.is_correct ? 'font-bold text-green-700' : 'text-gray-700'">{{ opt.option_text }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>