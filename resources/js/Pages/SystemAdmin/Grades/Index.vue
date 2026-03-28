<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    submissions: Object
});

// Khởi tạo Form để xử lý upload file Excel
const importForm = useForm({
    file: null,
});

// Hàm bắt sự kiện khi Admin chọn file
const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        importForm.file = file;
        importForm.post(route('system.grades.import'), {
            preserveScroll: true,
            onSuccess: () => {
                importForm.reset();
                event.target.value = null; // Reset lại input để có thể chọn lại file đó nếu cần
            },
            onError: () => {
                event.target.value = null; // Reset nếu có lỗi
            }
        });
    }
};
</script>

<template>
    <Head title="Đánh giá - Chấm bài" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-bold">{{ $page.props.flash.success }}</span>
                </div>

                <div v-if="$page.props.flash?.error || importForm.errors.file" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-bold">{{ $page.props.flash?.error || importForm.errors.file }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    
                    <div class="flex justify-between items-end mb-8 border-b pb-4">
                        <h2 class="text-2xl font-bold text-gray-800 uppercase">Đánh giá & Chấm điểm</h2>
                        
                        <div class="flex gap-3">
                            <label class="cursor-pointer px-4 py-2 bg-[#16a34a] hover:bg-green-700 text-white font-bold rounded shadow-sm text-[13px] uppercase tracking-wide transition flex items-center gap-2 relative" :class="{'opacity-75 pointer-events-none': importForm.processing}">
                                <span v-if="importForm.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                
                                {{ importForm.processing ? 'Đang nhập điểm...' : 'Nhập Điểm (Từ Excel)' }}
                                
                                <input type="file" class="hidden" accept=".xlsx, .xls, .csv" @change="handleFileUpload" :disabled="importForm.processing">
                            </label>
                        </div>
                    </div>

                    <h3 class="text-base font-bold text-gray-800 mb-3">Danh sách bài chờ chấm ({{ submissions.total }} bài):</h3>
                    <div class="overflow-x-auto border border-gray-300">
                        <table class="min-w-full divide-y divide-gray-300 text-center text-[13px]">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300 w-12">STT</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Mã NV</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Họ tên</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Phòng ban</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Khóa học</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Lớp</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Loại bài</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Ngày nộp</th>
                                    <th class="px-3 py-3 font-bold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-300">
                                <tr v-if="submissions.data.length === 0">
                                    <td colspan="9" class="px-3 py-8 text-gray-500 italic">Hiện không có bài nào đang chờ chấm.</td>
                                </tr>
                                <tr v-for="item in submissions.data" :key="item.id" class="hover:bg-gray-50 transition cursor-pointer" @click="router.get(route('system.grades.show', item.id))">
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.stt }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.emp_code }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-800 font-medium text-left">{{ item.name }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700 text-left">{{ item.department }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-800 text-left">{{ item.course }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.class_code }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">
                                        <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded text-xs font-bold">{{ item.type }}</span>
                                    </td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.submit_date }}</td>
                                    <td class="px-3 py-3">
                                        <Link @click.stop :href="route('system.grades.show', item.id)" class="text-[#0ea5e9] hover:text-blue-700 hover:underline font-bold text-[13px] uppercase">
                                            [ Chấm ]
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>