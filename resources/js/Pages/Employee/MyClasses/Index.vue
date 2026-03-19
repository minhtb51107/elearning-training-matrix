<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// MOCK DATA
const classes = ref([
    { id: 1, title: 'CHÍNH SÁCH NHÂN SỰ & LƯƠNG THƯỞNG', description: 'Khóa học bắt buộc cho nhân viên thử việc', statusText: 'Đã đăng ký - Chưa bắt đầu', progress: null, btn: 'BẮT ĐẦU HỌC' },
    { id: 2, title: 'QUẢN LÝ TRUYỀN THÔNG DỰ ÁN', date: '02/2026 - 12/2027', statusText: 'Đã đăng ký - Chưa bắt đầu', progress: null, btn: 'XEM CHI TIẾT' },
    { id: 3, title: 'QUẢN LÝ TRUYỀN THÔNG DỰ ÁN', date: '10/2024 - 12/2025', statusText: 'Đang học', progress: 50, progressText: '50%', btn: 'HỌC TIẾP' },
    { id: 4, title: 'QUẢN LÝ TRUYỀN THÔNG DỰ ÁN', date: '10/2024 - 12/2025', statusText: 'Hoàn thành', progress: 100, progressText: 'PASSED', btn: 'XEM KẾT QUẢ' },
    { id: 5, title: 'QUẢN LÝ TRUYỀN THÔNG DỰ ÁN', date: '10/2024 - 12/2025', statusText: 'Không đạt', progress: 100, progressText: 'FAILED', btn: 'XEM KẾT QUẢ', isFailed: true },
    { id: 6, title: 'QUẢN LÝ TRUYỀN THÔNG DỰ ÁN', date: '02/2026 - 12/2027', statusText: 'Đã đăng ký - Chưa bắt đầu', progress: null, btn: 'XEM CHI TIẾT' },
]);
</script>

<template>
    <Head title="Danh sách lớp học" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Danh sách lớp học</h2>

                    <div class="mb-8">
                        <label class="block text-base font-bold text-gray-800 mb-4">Bộ lọc:</label>
                        <div class="grid grid-cols-3 gap-6 w-3/4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Trạng thái:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500 text-gray-800">
                                    <option>Tất cả</option>
                                    <option>Đang học</option>
                                    <option>Hoàn thành</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Thời gian:</label>
                                <input type="text" placeholder="Chọn thời gian..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500 text-gray-400">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Từ khóa:</label>
                                <input type="text" placeholder="Nhập và chọn" class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500 placeholder-gray-400">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-8">
                        <div v-for="cls in classes" :key="cls.id" class="border border-gray-300 rounded overflow-hidden shadow-sm bg-white flex flex-col">
                            <div class="h-40 bg-gray-200 w-full overflow-hidden flex items-center justify-center">
                                <img src="https://placehold.co/600x300/0ea5e9/white?text=IMAGE" class="w-full h-full object-cover">
                            </div>
                            
                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-base font-bold text-gray-800 uppercase mb-2 line-clamp-2 leading-tight">{{ cls.title }}</h3>
                                
                                <div class="text-[13px] text-gray-600 mb-4 flex-1">
                                    <p v-if="cls.description" class="italic mb-1">{{ cls.description }}</p>
                                    <p v-if="cls.date" class="mb-1">Date: {{ cls.date }}</p>
                                    <p>Trạng thái: {{ cls.statusText }}</p>
                                    
                                    <div v-if="cls.progress !== null" class="mt-3 flex items-center gap-3">
                                        <div class="flex-1 bg-gray-200 h-4 border border-gray-300 relative">
                                            <div class="h-full bg-gray-400" :style="{ width: cls.progress + '%' }"></div>
                                            <span class="absolute inset-0 flex items-center justify-center text-[10px] font-bold" :class="{'text-gray-800': !cls.isFailed, 'text-red-700': cls.isFailed}">
                                                {{ cls.progressText }}
                                            </span>
                                        </div>
                                        <span v-if="cls.progressText === '50%'" class="text-xs font-bold text-gray-800">50%</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end mt-auto">
                                    <button class="border border-gray-400 text-gray-600 hover:text-gray-900 hover:border-gray-900 text-xs font-bold px-3 py-1.5 rounded transition uppercase">
                                        {{ cls.btn }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-center items-center gap-4 text-sm text-[#0ea5e9] font-medium">
                        <button class="hover:underline">&lt; Prev</button>
                        <button class="w-7 h-7 rounded bg-blue-100 font-bold flex items-center justify-center">1</button>
                        <button class="w-7 h-7 rounded hover:bg-gray-100 flex items-center justify-center text-gray-600">2</button>
                        <button class="w-7 h-7 rounded hover:bg-gray-100 flex items-center justify-center text-gray-600">3</button>
                        <button class="hover:underline">Next &gt;</button>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>