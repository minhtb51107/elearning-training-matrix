<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// MOCK DATA: Card khóa học
const courses = ref([
    { 
        id: 1, 
        image: 'https://placehold.co/600x300/0ea5e9/white?text=HR', 
        title: 'CHÍNH SÁCH NHÂN SỰ & LƯƠNG THƯỞNG', 
        description: 'Khóa học bắt buộc cho nhân viên thử việc', 
        date: '', 
        type: 'Bắt buộc' 
    },
    { 
        id: 2, 
        image: 'https://placehold.co/600x300/0ea5e9/white?text=PM', 
        title: 'QUẢN LÝ TRUYỀN THÔNG DỰ ÁN', 
        description: '', 
        date: '03/2026 - 03/2026', 
        type: 'Tự chọn' 
    },
    { 
        id: 3, 
        image: 'https://placehold.co/600x300/0ea5e9/white?text=SALES', 
        title: 'KỸ NĂNG BÁN HÀNG CHUYÊN SÂU', 
        description: '', 
        date: '04/2026 - 04/2026', 
        type: 'Bắt buộc' 
    },
    { 
        id: 4, 
        image: 'https://placehold.co/600x300/0ea5e9/white?text=PM2', 
        title: 'QUẢN LÝ TRUYỀN THÔNG DỰ ÁN', 
        description: '', 
        date: '10/2024 - 12/2025', 
        type: 'Tự chọn' 
    },
    { 
        id: 5, 
        image: 'https://placehold.co/600x300/0ea5e9/white?text=PM3', 
        title: 'QUẢN LÝ TRUYỀN THÔNG DỰ ÁN', 
        description: '', 
        date: '10/2024 - 12/2025', 
        type: 'Tự chọn' 
    },
]);
</script>

<template>
    <Head title="Danh sách khóa học" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Danh sách khóa học</h2>

                    <div class="mb-8">
                        <label class="block text-base font-bold text-gray-800 mb-4">Bộ lọc:</label>
                        <div class="grid grid-cols-3 gap-6 w-3/4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Trạng thái:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500 text-gray-800">
                                    <option>Tất cả</option>
                                    <option>Bắt buộc</option>
                                    <option>Tự chọn</option>
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
                        <div v-for="course in courses" :key="course.id" class="border border-gray-300 rounded overflow-hidden shadow-sm hover:shadow-md transition bg-white flex flex-col cursor-pointer" @click="router.get(route('employee.courses.show', course.id))">
                            
                            <div class="h-40 bg-gray-200 w-full overflow-hidden">
                                <img :src="course.image" class="w-full h-full object-cover" alt="Course Cover">
                            </div>
                            
                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-base font-bold text-gray-800 uppercase mb-2 line-clamp-2 leading-tight">{{ course.title }}</h3>
                                
                                <div class="text-[13px] text-gray-600 mb-4 flex-1">
                                    <p v-if="course.description" class="italic">{{ course.description }}</p>
                                    <p v-if="course.date">Date: {{ course.date }}</p>
                                    <p>Trạng thái: <span class="font-medium" :class="course.type === 'Bắt buộc' ? 'text-red-600' : 'text-gray-800'">{{ course.type }}</span></p>
                                </div>
                                
                                <div class="flex justify-end mt-auto">
                                    <button class="border border-gray-400 text-gray-600 hover:text-gray-900 hover:border-gray-900 text-xs font-bold px-3 py-1 rounded transition uppercase">
                                        Xem chi tiết
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