<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

// QUẢN LÝ DÒNG MỞ RỘNG (EXPANDABLE ROW)
const expandedRows = ref([1, 2]); // Mặc định mở dòng 1 và 2 giống ảnh
const toggleRow = (id) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
    } else {
        expandedRows.value.push(id);
    }
};

// MOCK DATA
const schedule = ref([
    { id: 1, date: 'T2 19/01/26', class: 'Sales nâng cao - L1', course: 'Kỹ năng bán hàng', instructor: 'N.V. Nam', format: 'Online', time: '08:00-10:00', status: 'Đang học', link: 'https://meet.google.com/ypx-bkys-ahs', room: null },
    { id: 2, date: 'T2 26/01/26', class: 'Sales nâng cao - L1', course: 'Kỹ năng bán hàng', instructor: 'N.V. Nam', format: 'Online', time: '08:00-10:00', status: 'Đang học', link: null, room: null },
    { id: 3, date: 'T5 05/02/26', class: 'DevOps cơ bản - L2', course: 'DevOps cơ bản', instructor: 'B.T. Hoa', format: 'Offline', time: '13:30-15:30', status: 'Đang học', link: null, room: '1001' },
]);

// MODAL CHI TIẾT
const showModal = ref(false);
const selectedSession = ref(null);

const openDetailModal = (session) => {
    selectedSession.value = session;
    showModal.value = true;
};
</script>

<template>
    <Head title="Lịch học của tôi" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-4">Lịch học của tôi</h2>

                    <div class="flex items-center gap-12 mb-8">
                        <label class="text-base font-bold text-gray-800">Bộ lọc:</label>
                        <div class="flex gap-8 text-[15px] text-gray-800">
                            <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                                <input type="radio" name="filter_time" class="border-gray-400 text-blue-600 focus:ring-blue-500"> Tuần này
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                                <input type="radio" name="filter_time" class="border-gray-400 text-blue-600 focus:ring-blue-500"> Tháng này
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                                <input type="radio" name="filter_time" class="border-gray-400 text-blue-600 focus:ring-blue-500" checked> Tất cả
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end mb-4">
                        <div class="flex bg-[#e5e7eb] rounded border border-gray-400 overflow-hidden">
                            <button class="px-6 py-1.5 text-[13px] font-bold text-gray-700 hover:bg-gray-300 border-r border-gray-400 transition">Print</button>
                            <button class="px-6 py-1.5 text-[13px] font-bold text-gray-700 hover:bg-gray-300 border-r border-gray-400 transition">Excel</button>
                            <button class="px-6 py-1.5 text-[13px] font-bold text-gray-700 hover:bg-gray-300 border-r border-gray-400 transition">PDF</button>
                            <button class="px-6 py-1.5 text-[13px] font-bold text-gray-700 hover:bg-gray-300 transition">CSV</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-400 border-b-0">
                        <table class="min-w-full text-center text-[13px]">
                            <thead class="bg-gray-50 border-b border-gray-400">
                                <tr>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400">STT</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400">Ngày</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 text-left">Lớp học</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 text-left">Khóa học</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400">Giảng viên</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400">Hình thức</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400">Thời gian</th>
                                    <th class="px-4 py-3 font-bold text-gray-900">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="item in schedule" :key="item.id">
                                    <tr class="bg-white border-b border-gray-400 hover:bg-gray-50 transition cursor-pointer" @click="toggleRow(item.id)">
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-700">
                                            <span class="mr-1 text-gray-500 font-bold border border-gray-400 rounded-full px-1 text-[10px] inline-flex items-center justify-center w-4 h-4">{{ expandedRows.includes(item.id) ? '-' : '+' }}</span>
                                            {{ item.id }}
                                        </td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-700">{{ item.date }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-800 text-left">{{ item.class }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-700 text-left">{{ item.course }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-700">{{ item.instructor }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-700">{{ item.format }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-700">{{ item.time }}</td>
                                        <td class="px-4 py-3 text-gray-700">{{ item.status }}</td>
                                    </tr>
                                    
                                    <tr v-if="expandedRows.includes(item.id)" class="bg-white border-b border-gray-400">
                                        <td colspan="8" class="px-6 py-4 text-left text-[13px] text-gray-800">
                                            <div class="flex flex-col gap-2">
                                                <div v-if="item.link" class="flex gap-4">
                                                    <span class="font-bold w-32">Link học trực tuyến:</span>
                                                    <a :href="item.link" target="_blank" class="text-blue-500 hover:underline">{{ item.link }}</a>
                                                </div>
                                                <div v-if="item.room" class="flex gap-4">
                                                    <span class="font-bold w-32">Phòng học:</span>
                                                    <span>{{ item.room }}</span>
                                                </div>
                                                <div class="flex gap-4">
                                                    <span class="font-bold w-32">Chi tiết:</span>
                                                    <button @click="openDetailModal(item)" class="text-[#0ea5e9] hover:underline font-bold">Chi tiết</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-center items-center gap-4 text-sm text-[#0ea5e9] font-medium">
                        <button class="hover:underline">&lt; Prev</button>
                        <button class="w-7 h-7 rounded bg-blue-100 font-bold flex items-center justify-center">1</button>
                        <button class="w-7 h-7 rounded hover:bg-gray-100 flex items-center justify-center text-gray-600">2</button>
                        <button class="w-7 h-7 rounded hover:bg-gray-100 flex items-center justify-center text-gray-600">3</button>
                        <button class="hover:underline">Next &gt;</button>
                    </div>

                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="showModal = false" maxWidth="md">
            <div class="p-8 bg-white border border-gray-300">
                <h3 class="text-lg font-bold text-gray-800 uppercase mb-4 border-b border-gray-300 pb-2">THÔNG TIN BUỔI HỌC:</h3>
                
                <h4 class="text-lg font-bold text-gray-800 mb-2">Sales nâng cao - Lớp L1</h4>
                <p class="text-[14px] font-bold text-gray-800 mb-6">Buổi 2/5 trong lộ trình lớp học</p>

                <div class="text-[14px] text-gray-800 font-bold mb-6">
                    <p class="mb-1">Nội dung buổi học:</p>
                    <ul class="list-disc pl-5 font-normal">
                        <li>Nhiệm vụ của nhân viên sales</li>
                        <li>Các chỉ số đánh giá hiệu quả bán hàng</li>
                    </ul>
                </div>

                <div class="text-[14px] text-gray-800 font-bold">
                    <p class="mb-1">Ghi chú:</p>
                    <p class="font-normal">Buổi học có bài tập tình huống cuối buổi</p>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>