<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// MOCK DATA
const assignments = ref([
    { id: 1, stt: 1, emp_code: 'NV-2021-0045', name: 'Nguyễn Văn An', department: 'Kinh doanh', course: 'Quy trình nội bộ', class_code: 'QTNB-L1', type: 'Cuối khóa', submit_date: '18/03/2026' },
    { id: 2, stt: 2, emp_code: 'NV-2020-0112', name: 'Trần Minh Đức', department: 'Nhân sự', course: 'Văn hóa doanh nghiệp', class_code: 'VH-L2', type: 'Cuối khóa', submit_date: '17/03/2026' },
    { id: 3, stt: 3, emp_code: 'NV-2019-0321', name: 'Lê Thị Mai', department: 'IT', course: 'An toàn thông tin', class_code: 'ATTT-L1', type: 'Giữa khóa', submit_date: '16/03/2026' },
    { id: 4, stt: 4, emp_code: 'NV-2021-0189', name: 'Phạm Quốc Huy', department: 'Kinh doanh', course: 'Kỹ năng bán hàng', class_code: 'SALE-L3', type: 'Cuối khóa', submit_date: '15/03/2026' },
]);
</script>

<template>
    <Head title="Đánh giá - Chấm bài" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-8 border-b pb-4 uppercase">Đánh giá</h2>

                    <div class="mb-8">
                        <label class="block text-base font-bold text-gray-800 mb-4">Bộ lọc:</label>
                        <div class="grid grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Khóa học:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-500">
                                    <option>Yêu cầu đào tạo</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Lớp học:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-500">
                                    <option>Phòng kinh doanh</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Loại bài:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-600">
                                    <option>Cuối khóa</option>
                                    <option>Giữa khóa</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Từ khóa:</label>
                                <input type="text" placeholder="Nhập và chọn" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                            </div>
                        </div>
                    </div>

                    <h3 class="text-base font-bold text-gray-800 mb-3">Danh sách bài cần chấm:</h3>
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
                                <tr v-for="item in assignments" :key="item.id" class="hover:bg-gray-50 transition cursor-pointer" @click="router.get(route('system.grades.show', item.id))">
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.stt }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.emp_code }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-800 font-medium text-left">{{ item.name }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700 text-left">{{ item.department }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-800 text-left">{{ item.course }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.class_code }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.type }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.submit_date }}</td>
                                    <td class="px-3 py-3">
                                        <Link @click.stop :href="route('system.grades.show', item.id)" class="text-[#0ea5e9] hover:underline font-bold text-sm">
                                            [ Chấm ]
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-for="i in 3" :key="'empty-'+i">
                                    <td class="px-3 py-5 border-r border-gray-300"></td>
                                    <td class="px-3 py-5 border-r border-gray-300"></td>
                                    <td class="px-3 py-5 border-r border-gray-300"></td>
                                    <td class="px-3 py-5 border-r border-gray-300"></td>
                                    <td class="px-3 py-5 border-r border-gray-300"></td>
                                    <td class="px-3 py-5 border-r border-gray-300"></td>
                                    <td class="px-3 py-5 border-r border-gray-300"></td>
                                    <td class="px-3 py-5 border-r border-gray-300"></td>
                                    <td class="px-3 py-5"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-center items-center gap-4 text-sm text-[#0ea5e9] font-medium">
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