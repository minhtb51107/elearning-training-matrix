<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// MOCK DATA: Lấy chính xác dữ liệu từ ảnh thiết kế
const courseInfo = {
    code: 'KH001',
    name: 'Kỹ năng bán hàng',
    target_audience: 'Toàn phòng ban',
    format: 'Offline',
    scope: 'Phòng Kinh doanh',
    duration: '8 giờ'
};

const classes = [
    { code: 'KH001_01', name: 'Lớp 1', instructor: 'Anh A', time: '15/01', capacity: 25 },
    { code: 'KH001_02', name: 'Lớp 2', instructor: 'Chị B', time: '18/01', capacity: 20 }
];

const materials = [
    { name: 'File:ky_nang_ban_hang.pdf', url: '#' },
    { name: 'Link: .......', url: '#' }
];
</script>

<template>
    <Head :title="'Chi tiết khóa học: ' + courseInfo.name" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <div class="mb-6">
                        <Link :href="route('department.courses.index')" class="text-xl font-bold text-gray-800 hover:text-gray-500 transition">
                            &lt; Quay lại
                        </Link>
                    </div>

                    <div class="mb-8">
                        <h3 class="font-bold text-lg text-gray-800 mb-3">Thông tin chung:</h3>
                        <div class="bg-[#e5e7eb] p-6 border border-gray-400 rounded-sm">
                            <div class="grid grid-cols-2 gap-y-3 gap-x-12 text-gray-800">
                                <div>
                                    <span class="font-bold inline-block w-28">Mã khóa học:</span>
                                    <span>{{ courseInfo.code }}</span>
                                </div>
                                <div>
                                    <span class="font-bold inline-block w-24">Hình thức:</span>
                                    <span>{{ courseInfo.format }}</span>
                                </div>
                                <div>
                                    <span class="font-bold inline-block w-28">Tên khóa học:</span>
                                    <span>{{ courseInfo.name }}</span>
                                </div>
                                <div>
                                    <span class="font-bold inline-block w-24">Phạm vi:</span>
                                    <span>{{ courseInfo.scope }}</span>
                                </div>
                                <div>
                                    <span class="font-bold inline-block w-28">Đối tượng:</span>
                                    <span>{{ courseInfo.target_audience }}</span>
                                </div>
                                <div>
                                    <span class="font-bold inline-block w-24">Số giờ:</span>
                                    <span>{{ courseInfo.duration }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="font-bold text-lg text-gray-800 mb-3">Danh sách lớp học thuộc khóa học:</h3>
                        <div class="overflow-x-auto border border-gray-400">
                            <table class="min-w-full divide-y divide-gray-400 text-center text-sm">
                                <thead class="bg-[#fcd38e]">
                                    <tr>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/5">Mã lớp</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/5">Tên lớp</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/5">Giảng viên</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-1/5">Thời gian</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 w-1/5">Số lượng</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-400">
                                    <tr v-for="(cls, index) in classes" :key="index" class="hover:bg-gray-50 transition cursor-default">
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ cls.code }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ cls.name }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ cls.instructor }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-800">{{ cls.time }}</td>
                                        <td class="px-4 py-3 text-gray-800">{{ cls.capacity }}</td>
                                    </tr>
                                    <tr v-for="i in 3" :key="'empty-'+i">
                                        <td class="px-4 py-5 border-r border-gray-400"></td>
                                        <td class="px-4 py-5 border-r border-gray-400"></td>
                                        <td class="px-4 py-5 border-r border-gray-400"></td>
                                        <td class="px-4 py-5 border-r border-gray-400"></td>
                                        <td class="px-4 py-5"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-lg text-gray-800 mb-2">Tài liệu khóa học:</h3>
                        <ul class="space-y-1 ml-1 text-[15px]">
                            <li v-for="(mat, index) in materials" :key="index">
                                - <a :href="mat.url" class="text-blue-500 hover:underline">{{ mat.name }}</a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>