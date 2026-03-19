<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'; // Đã thêm router ở đây

// MOCK DATA: Chép chính xác 100% từ thiết kế của bạn
const mockCourses = ref([
    { code: 'KH001', name: 'Kỹ năng bán hàng', request_date: '', format: 'Offline', start_date: '', classes: 2, status: 'Đang mở' },
    { code: 'KH002', name: 'An toàn lao động', request_date: '', format: 'Online', start_date: '', classes: 1, status: 'Đang triển khai' },
    { code: 'KH003', name: 'Excel nâng cao', request_date: '', format: 'Blended', start_date: '', classes: 3, status: 'Kết thúc' },
]);

// Đổ màu full ô theo đúng mockup
const getStatusBgClass = (status) => {
    if (status === 'Đang mở') return 'bg-[#b7eb8f] text-gray-900'; // Xanh lá nhạt
    if (status === 'Đang triển khai') return 'bg-[#fffb8f] text-gray-900'; // Vàng nhạt
    if (status === 'Kết thúc') return 'bg-[#ff4d4f] text-white'; // Đỏ
    return 'bg-white text-gray-800';
};
</script>

<template>
    <Head title="Khóa học của phòng ban" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-4">Khóa học của phòng ban:</h2>

                    <div class="mb-6">
                        <label class="block text-base font-bold text-gray-800 mb-2">Bộ lọc:</label>
                        <div class="flex flex-wrap items-end gap-4">
                            <div class="flex-1 min-w-[200px]">
                                <label class="block text-sm text-gray-600 mb-1">Khóa học:</label>
                                <input type="text" class="w-full border-gray-400 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Nhập và chọn" />
                            </div>
                            <div class="w-36">
                                <label class="block text-sm text-gray-600 mb-1">Từ ngày:</label>
                                <input type="date" class="w-full border-gray-400 rounded-md text-sm text-gray-600 focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <div class="w-36">
                                <label class="block text-sm text-gray-600 mb-1">Đến ngày:</label>
                                <input type="date" class="w-full border-gray-400 rounded-md text-sm text-gray-600 focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <div class="w-36">
                                <label class="block text-sm text-gray-600 mb-1">Hình thức:</label>
                                <select class="w-full border-gray-400 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tất cả</option>
                                    <option value="offline">Offline</option>
                                    <option value="online">Online</option>
                                    <option value="blended">Blended</option>
                                </select>
                            </div>
                            <div class="w-40">
                                <label class="block text-sm text-gray-600 mb-1">Trạng thái:</label>
                                <select class="w-full border-gray-400 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tất cả</option>
                                    <option value="open">Đang mở</option>
                                    <option value="in_progress">Đang triển khai</option>
                                    <option value="completed">Kết thúc</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-400 mt-8">
                        <table class="min-w-full divide-y divide-gray-400">
                            <thead class="bg-[#fcd38e]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 border-r border-gray-400 w-24">Mã KH</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 border-r border-gray-400">Tên khóa học</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 border-r border-gray-400 w-32">Ngày gửi YC</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 border-r border-gray-400 w-28">Hình thức</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 border-r border-gray-400 w-32">Ngày bắt đầu</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 border-r border-gray-400 w-24">Số lớp</th>
                                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-900 w-36">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-400">
                                <tr v-for="(course, index) in mockCourses" :key="index" class="hover:bg-gray-50 transition cursor-pointer" @click="router.get(route('department.courses.show', course.code))">
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-700">{{ course.code }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-800">{{ course.name }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-600">{{ course.request_date }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-700">{{ course.format }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-700">{{ course.start_date }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-700">{{ course.classes }}</td>
                                    
                                    <td :class="['px-4 py-3 text-sm font-medium border-l border-gray-400', getStatusBgClass(course.status)]">
                                        {{ course.status }}
                                    </td>
                                </tr>
                                <tr v-for="i in 3" :key="'empty-'+i">
                                    <td class="px-4 py-6 border-r border-gray-400"></td>
                                    <td class="px-4 py-6 border-r border-gray-400"></td>
                                    <td class="px-4 py-6 border-r border-gray-400"></td>
                                    <td class="px-4 py-6 border-r border-gray-400"></td>
                                    <td class="px-4 py-6 border-r border-gray-400"></td>
                                    <td class="px-4 py-6 border-r border-gray-400"></td>
                                    <td class="px-4 py-6"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-center">
                        <Link :href="route('department.requests.create')" class="text-[#d97706] hover:text-orange-700 font-bold text-[15px] uppercase tracking-wide">
                            [ + GỬI YÊU CẦU ĐÀO TẠO MỚI ]
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>