<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// MOCK DATA
const student = {
    name: 'Nguyễn Văn An',
    emp_code: 'NV-2021-0045',
    department: 'Kinh doanh',
    submit_date: '18/03/2026',
    course: 'Quy trình nội bộ',
    class_code: 'QTNB-L1',
    type: 'Bài kiểm tra CUỐI KHÓA'
};

const form = ref({
    score: '',
    remark: ''
});
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
                                <p><span class="font-bold inline-block w-24">Họ tên:</span> {{ student.name }}</p>
                                <p><span class="font-bold inline-block w-24">Mã NV:</span> {{ student.emp_code }}</p>
                                <p><span class="font-bold inline-block w-24">Phòng ban:</span> {{ student.department }}</p>
                                <p><span class="font-bold inline-block w-24">Ngày nộp:</span> {{ student.submit_date }}</p>
                            </div>
                            <div>
                                <p><span class="font-bold inline-block w-24">Khóa học:</span> {{ student.course }}</p>
                                <p><span class="font-bold inline-block w-24">Lớp học:</span> {{ student.class_code }}</p>
                                <p><span class="font-bold inline-block w-24">Loại bài:</span> <span class="uppercase">{{ student.type }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-10">
                        <h3 class="font-bold text-[15px] text-gray-800 mb-4 border-b pb-2">Nội dung bài làm:</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <p class="font-bold text-sm text-gray-800 mb-1">Câu 1:</p>
                                <p class="text-sm text-gray-700 mb-2">[ Nội dung câu hỏi ]</p>
                                <textarea disabled rows="3" class="w-full bg-gray-50 border-gray-300 rounded-sm text-gray-400 italic text-sm resize-none" placeholder="[ Text bài làm dài / nội dung upload / link file ]"></textarea>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-gray-800 mb-1">Câu 2:</p>
                                <p class="text-sm text-gray-700 mb-2">[ Nội dung câu hỏi ]</p>
                                <textarea disabled rows="3" class="w-full bg-gray-50 border-gray-300 rounded-sm text-gray-400 italic text-sm resize-none" placeholder="[ Text bài làm dài / nội dung upload / link file ]"></textarea>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-gray-800 mb-1">Câu 3:</p>
                                <p class="text-sm text-gray-700 mb-2">[ Nội dung câu hỏi ]</p>
                                <textarea disabled rows="3" class="w-full bg-gray-50 border-gray-300 rounded-sm text-gray-400 italic text-sm resize-none" placeholder="[ Text bài làm dài / nội dung upload / link file ]"></textarea>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-[15px] text-gray-800 mb-4 border-b pb-2">Đánh giá & Chấm điểm:</h3>
                        <form @submit.prevent>
                            <div class="mb-6 flex items-center gap-4">
                                <label class="font-bold text-sm text-gray-800 w-24">Điểm: <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-2">
                                    <input v-model="form.score" type="number" min="0" max="100" class="border-gray-400 rounded-sm text-sm w-24 focus:ring-gray-500 text-center font-bold">
                                    <span class="font-bold text-gray-800 text-base">/ 100</span>
                                </div>
                            </div>
                            <div class="mb-8">
                                <label class="block font-bold text-sm text-gray-800 mb-2">Nhận xét:</label>
                                <textarea v-model="form.remark" rows="4" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 placeholder-gray-400 italic" placeholder="[ Textarea nhập nhận xét của người chấm ]"></textarea>
                            </div>

                            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                                <Link :href="route('system.grades.index')" class="text-[#d97706] hover:text-orange-700 font-bold text-[15px] uppercase tracking-wide transition">
                                    [ HỦY ]
                                </Link>
                                <button type="button" class="text-[#16a34a] hover:text-green-700 font-bold text-[15px] uppercase tracking-wide transition">
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