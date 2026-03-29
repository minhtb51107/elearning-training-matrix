<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    schedule: Object,
    filters: Object
});

// QUẢN LÝ DÒNG MỞ RỘNG (EXPANDABLE ROW)
const expandedRows = ref([]); // Mặc định đóng hết
const toggleRow = (id) => {
    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id);
    } else {
        expandedRows.value.push(id);
    }
};

// BỘ LỌC THỜI GIAN
const filterTime = ref(props.filters?.filter_time || 'Tất cả');

watch(filterTime, (newVal) => {
    router.get(route('employee.my-schedule'), { filter_time: newVal }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
});

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
                                <input v-model="filterTime" type="radio" value="Tuần này" class="border-gray-400 text-blue-600 focus:ring-blue-500"> Tuần này
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                                <input v-model="filterTime" type="radio" value="Tháng này" class="border-gray-400 text-blue-600 focus:ring-blue-500"> Tháng này
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600">
                                <input v-model="filterTime" type="radio" value="Tất cả" class="border-gray-400 text-blue-600 focus:ring-blue-500"> Tất cả
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end mb-4">
                        <div class="flex bg-[#e5e7eb] rounded border border-gray-400 overflow-hidden">
                            <button onclick="window.print()" class="px-6 py-1.5 text-[13px] font-bold text-gray-700 hover:bg-gray-300 border-r border-gray-400 transition">Print</button>
                            
                            <a :href="route('employee.my-schedule.export', { format: 'excel', filter_time: filterTime })" class="px-6 py-1.5 text-[13px] font-bold text-gray-700 hover:bg-gray-300 border-r border-gray-400 transition inline-flex items-center">
                                Excel
                            </a>
                            
                            <a :href="route('employee.my-schedule.export', { format: 'pdf', filter_time: filterTime })" class="px-6 py-1.5 text-[13px] font-bold text-gray-700 hover:bg-gray-300 border-r border-gray-400 transition inline-flex items-center">
                                PDF
                            </a>
                            
                            <a :href="route('employee.my-schedule.export', { format: 'csv', filter_time: filterTime })" class="px-6 py-1.5 text-[13px] font-bold text-gray-700 hover:bg-gray-300 transition inline-flex items-center">
                                CSV
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-gray-400 border-b-0">
                        <table class="min-w-full text-center text-[13px]">
                            <thead class="bg-gray-50 border-b border-gray-400">
                                <tr>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-12">STT</th>
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
                                <tr v-if="schedule.data.length === 0">
                                    <td colspan="8" class="px-4 py-8 text-gray-500 italic text-center border-b border-gray-400">Hiện không có lịch học nào.</td>
                                </tr>
                                <template v-for="(item, index) in schedule.data" :key="item.id">
                                    <tr class="bg-white border-b border-gray-400 hover:bg-gray-50 transition cursor-pointer" @click="toggleRow(item.id)">
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-700">
                                            <span class="mr-1 text-gray-500 font-bold border border-gray-400 rounded-full px-1 text-[10px] inline-flex items-center justify-center w-4 h-4">{{ expandedRows.includes(item.id) ? '-' : '+' }}</span>
                                            {{ index + 1 }}
                                        </td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-700 font-bold text-blue-600">{{ item.date }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-800 text-left font-medium">{{ item.class }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-700 text-left">{{ item.course }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-700">{{ item.instructor }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-700">{{ item.format }}</td>
                                        <td class="px-4 py-3 border-r border-gray-400 text-gray-700 font-medium">{{ item.time }}</td>
                                        <td class="px-4 py-3 text-gray-700">
                                            <span class="px-2 py-1 rounded text-xs font-bold" 
                                                  :class="item.status === 'Đang học' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'">
                                                {{ item.status }}
                                            </span>
                                        </td>
                                    </tr>
                                    
                                    <tr v-if="expandedRows.includes(item.id)" class="bg-blue-50/30 border-b border-gray-400">
                                        <td colspan="8" class="px-8 py-4 text-left text-[13px] text-gray-800 shadow-inner">
                                            <div class="flex flex-col gap-3">
                                                <div v-if="item.link" class="flex gap-4 items-center">
                                                    <span class="font-bold w-32 text-gray-600">Link trực tuyến:</span>
                                                    <a :href="item.link" target="_blank" class="text-blue-600 font-medium hover:underline bg-blue-100 px-3 py-1 rounded">{{ item.link }}</a>
                                                </div>
                                                <div v-if="item.room" class="flex gap-4 items-center">
                                                    <span class="font-bold w-32 text-gray-600">Phòng học:</span>
                                                    <span class="bg-gray-200 px-3 py-1 rounded font-medium">{{ item.room }}</span>
                                                </div>
                                                <div class="flex gap-4 mt-2 border-t border-gray-200 pt-3">
                                                    <span class="font-bold w-32 text-gray-600">Chi tiết:</span>
                                                    <button @click="openDetailModal(item)" class="text-[#0ea5e9] hover:underline font-bold bg-white border border-sky-200 px-4 py-1 rounded shadow-sm uppercase tracking-wide">Xem nội dung bài học</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-center items-center gap-2 text-sm font-medium" v-if="schedule.links && schedule.links.length > 3">
                        <Component v-for="(link, index) in schedule.links" :key="index"
                                   :is="link.url ? 'Link' : 'span'" 
                                   :href="link.url" 
                                   v-html="link.label" 
                                   class="w-8 h-8 rounded flex items-center justify-center transition-colors" 
                                   :class="{
                                       'bg-blue-100 text-blue-700 font-bold border border-blue-300': link.active, 
                                       'text-gray-400 cursor-not-allowed': !link.url,
                                       'text-gray-600 hover:bg-gray-100': link.url && !link.active
                                   }" />
                    </div>

                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="showModal = false" maxWidth="md">
            <div class="p-8 bg-white border border-gray-300 shadow-2xl">
                <h3 class="text-lg font-bold text-gray-800 uppercase mb-4 border-b border-gray-300 pb-2">THÔNG TIN BUỔI HỌC</h3>
                
                <h4 class="text-lg font-bold text-blue-700 mb-2">{{ selectedSession?.course }} - {{ selectedSession?.class }}</h4>
                <p class="text-[14px] font-bold text-gray-600 mb-6 bg-gray-100 inline-block px-3 py-1 rounded">{{ selectedSession?.title }}</p>

                <div class="text-[14px] text-gray-800 font-bold mb-6">
                    <p class="mb-2 text-gray-500 uppercase tracking-wider text-xs">Thời gian & Hình thức:</p>
                    <ul class="list-disc pl-5 font-normal space-y-1 mb-4">
                        <li><strong class="font-medium text-gray-700">Ngày:</strong> {{ selectedSession?.date }}</li>
                        <li><strong class="font-medium text-gray-700">Giờ học:</strong> {{ selectedSession?.time }}</li>
                        <li><strong class="font-medium text-gray-700">Hình thức:</strong> {{ selectedSession?.format }} 
                            <span v-if="selectedSession?.room"> (Phòng: {{ selectedSession?.room }})</span>
                        </li>
                        <li><strong class="font-medium text-gray-700">Giảng viên:</strong> {{ selectedSession?.instructor }}</li>
                    </ul>
                </div>

                <div class="flex justify-end mt-6 pt-4 border-t border-gray-200">
                    <button @click="showModal = false" class="px-6 py-2 bg-gray-200 text-gray-700 font-bold rounded hover:bg-gray-300 transition-colors uppercase text-sm">Đóng</button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>