<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PaperClipIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    submission: Object
});

const form = useForm({
    score: props.submission.score || '',
    feedback: props.submission.feedback || ''
});

const submitGrade = () => {
    form.put(route('system.grades.update', props.submission.id));
};
</script>

<template>
    <Head title="Chấm bài học viên" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 border border-gray-200">
                    
                    <div class="mb-8">
                        <Link :href="route('system.grades.index')" class="text-[15px] font-bold text-gray-800 hover:text-gray-500 transition uppercase">
                            &lt; QUAY LẠI DANH SÁCH BÀI CẦN CHẤM
                        </Link>
                    </div>

                    <h2 class="text-xl font-bold text-gray-800 mb-6 uppercase border-b pb-4">CHẤM BÀI HỌC VIÊN</h2>

                    <div class="bg-[#e5e7eb] p-6 rounded-sm mb-8">
                        <h3 class="font-bold text-[15px] text-gray-800 mb-4">Thông tin học viên:</h3>
                        <div class="grid grid-cols-2 gap-x-12 gap-y-2 text-[14px] text-gray-800">
                            <div>
                                <p><span class="font-bold inline-block w-24">Họ tên:</span> {{ submission.student_name }}</p>
                                <p><span class="font-bold inline-block w-24">Mã NV:</span> {{ submission.emp_code }}</p>
                                <p><span class="font-bold inline-block w-24">Phòng ban:</span> {{ submission.department }}</p>
                                <p><span class="font-bold inline-block w-24">Ngày nộp:</span> {{ submission.submit_date }}</p>
                            </div>
                            <div>
                                <p><span class="font-bold inline-block w-24">Khóa học:</span> {{ submission.course }}</p>
                                <p><span class="font-bold inline-block w-24">Lớp học:</span> {{ submission.class_code }}</p>
                                <p><span class="font-bold inline-block w-24">Loại bài:</span> <span class="uppercase font-bold text-indigo-700">{{ submission.type }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-10">
                        <h3 class="font-bold text-[15px] text-gray-800 mb-4 border-b pb-2">Nội dung bài làm:</h3>
                        
                        <div class="space-y-6">
                            <div v-for="(question, index) in submission.questions" :key="index" class="border-b pb-6 border-gray-100">
                                <p class="font-bold text-sm text-gray-800 mb-1">Câu {{ index + 1 }}:</p>
                                <p class="text-sm text-blue-800 mb-3 bg-blue-50 p-3 rounded border border-blue-100 whitespace-pre-wrap">{{ question }}</p>
                                
                                <p class="font-bold text-xs text-gray-500 uppercase mb-2">Bài làm của học viên:</p>
                                <div class="w-full bg-gray-50 border border-gray-300 rounded-sm p-4 text-gray-800 text-sm whitespace-pre-wrap min-h-[80px]">
                                    {{ submission.answers ? submission.answers[index] : 'Chưa có câu trả lời text.' }}
                                </div>
                            </div>

                            <div v-if="submission.student_file" class="mt-6 pt-2">
                                <p class="font-bold text-sm text-gray-800 mb-2">File đính kèm:</p>
                                <a :href="submission.student_file" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50 text-blue-600 font-bold text-sm transition shadow-sm">
                                    <PaperClipIcon class="w-5 h-5" /> Tải về / Xem file bài làm
                                </a>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-[15px] text-gray-800 mb-4 border-b pb-2">Đánh giá & Chấm điểm:</h3>
                        <form @submit.prevent="submitGrade">
                            <div class="mb-6 flex items-center gap-4">
                                <label class="font-bold text-sm text-gray-800 w-24">Điểm số: <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-2">
                                    <input v-model="form.score" type="number" min="0" max="100" required class="border-gray-400 rounded-sm text-sm w-24 focus:ring-gray-500 text-center font-bold text-lg">
                                    <span class="font-bold text-gray-800 text-base">/ 100</span>
                                </div>
                                <span class="ml-4 text-xs text-gray-500">(Điểm cần đạt để qua môn: <strong class="text-gray-800">{{ submission.pass_score }}</strong>)</span>
                            </div>
                            <div v-if="form.errors.score" class="text-red-500 text-sm mb-4">{{ form.errors.score }}</div>

                            <div class="mb-8">
                                <label class="block font-bold text-sm text-gray-800 mb-2">Nhận xét / Lời phê:</label>
                                <textarea v-model="form.feedback" rows="4" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 placeholder-gray-400 italic" placeholder="Góp ý cho bài làm của học viên..."></textarea>
                            </div>

                            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                                <Link :href="route('system.grades.index')" class="text-[#d97706] hover:text-orange-700 font-bold text-[15px] uppercase tracking-wide transition">
                                    [ HỦY ]
                                </Link>
                                <button type="submit" :disabled="form.processing" class="text-[#16a34a] hover:text-green-700 font-bold text-[15px] uppercase tracking-wide transition disabled:opacity-50 flex items-center gap-2">
                                    <span v-if="form.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-green-700"></span>
                                    [ LƯU & GỬI KẾT QUẢ ]
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>