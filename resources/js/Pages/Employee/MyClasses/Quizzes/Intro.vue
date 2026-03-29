<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ClockIcon, ChartBarIcon, DocumentTextIcon, ChevronLeftIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    classInfo: Object,
    quiz: Object
});

const form = useForm({});

const startQuiz = () => {
    form.post(route('employee.my-classes.quizzes.start', [props.classInfo.id, props.quiz.id]));
};
</script>

<template>
    <Head :title="quiz.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('employee.my-classes.show', classInfo.id)" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-blue-600">
                    <ChevronLeftIcon class="w-4 h-4" /> Quay lại lớp học
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-xl sm:rounded-2xl p-8 text-center border border-gray-100">
                    <div class="mx-auto w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mb-6">
                        <DocumentTextIcon class="w-8 h-8" />
                    </div>
                    
                    <h1 class="text-3xl font-black text-gray-900 mb-2">{{ quiz.title }}</h1>
                    <p class="text-gray-500 font-medium mb-8">Lớp: {{ classInfo.code }} - Học viên: {{ $page.props.auth.user.name }}</p>
                    
                    <div class="grid grid-cols-2 gap-4 max-w-lg mx-auto mb-10">
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <ClockIcon class="w-6 h-6 text-gray-400 mx-auto mb-2" />
                            <div class="text-sm text-gray-500 uppercase font-bold">Thời gian</div>
                            <div class="text-xl font-black text-gray-900">{{ quiz.duration_minutes }} Phút</div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <ChartBarIcon class="w-6 h-6 text-gray-400 mx-auto mb-2" />
                            <div class="text-sm text-gray-500 uppercase font-bold">Điểm đạt</div>
                            <div class="text-xl font-black text-green-600">{{ quiz.pass_score }} / 100</div>
                        </div>
                    </div>

                    <div class="bg-blue-50 text-blue-800 p-4 rounded-lg text-sm mb-8 text-left inline-block w-full max-w-lg">
                        <p class="font-bold mb-1">Lưu ý trước khi làm bài:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Thời gian sẽ bắt đầu đếm ngược ngay khi bạn bấm nút Bắt đầu.</li>
                            <li>Nếu lỡ thoát trang web, bạn vẫn có thể vào lại để làm tiếp (nếu còn giờ).</li>
                            <li>Hệ thống sẽ tự động nộp bài khi hết giờ.</li>
                        </ul>
                    </div>

                    <form @submit.prevent="startQuiz">
                        <button type="submit" :disabled="form.processing" class="px-8 py-4 bg-indigo-600 text-white text-lg font-black rounded-xl hover:bg-indigo-700 shadow-lg transform transition active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2 mx-auto w-full max-w-lg">
                            <span v-if="form.processing" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
                            BẮT ĐẦU LÀM BÀI
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>