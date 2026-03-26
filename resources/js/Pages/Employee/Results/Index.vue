<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    results: Object,
    filters: Object,
    auth: Object
});

// LOGIC BỘ LỌC
const searchForm = ref({
    year: props.filters?.year || 'Tất cả',
    status: props.filters?.status || 'Tất cả',
    keyword: props.filters?.keyword || ''
});

let searchTimeout = null;
watch(searchForm, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('employee.results'), newValue, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }, 300);
}, { deep: true });

// XỬ LÝ MODAL (RẼ NHÁNH THEO LOẠI)
const showModal = ref(false);
const modalType = ref(''); // 'cert', 'failed', 'pending'
const selectedRow = ref(null);

const handleAction = (item) => {
    selectedRow.value = item;
    if (item.status === 'ĐẠT') {
        modalType.value = 'cert';
    } else if (item.status === 'KHÔNG ĐẠT') {
        modalType.value = 'failed';
    } else {
        modalType.value = 'pending';
    }
    showModal.value = true;
};
</script>

<template>
    <Head title="Kết quả & Chứng chỉ" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-4">Kết quả & Chứng chỉ của tôi</h2>

                    <div class="mb-8">
                        <label class="block text-base font-bold text-gray-800 mb-4">Bộ lọc:</label>
                        <div class="grid grid-cols-4 gap-6">
                            <div>
                                <label class="block text-[13px] text-gray-600 mb-1">Năm:</label>
                                <select v-model="searchForm.year" class="w-full border-gray-400 rounded-sm text-[13px] focus:ring-gray-500 text-gray-800">
                                    <option value="Tất cả">Tất cả</option>
                                    <option>2026</option>
                                    <option>2025</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[13px] text-gray-600 mb-1">Kết quả:</label>
                                <select v-model="searchForm.status" class="w-full border-gray-400 rounded-sm text-[13px] focus:ring-gray-500 text-gray-800">
                                    <option>Tất cả</option>
                                    <option>Đạt</option>
                                    <option>Không đạt</option>
                                    <option>Chờ chấm</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[13px] text-gray-600 mb-1">Từ khóa:</label>
                                <input v-model="searchForm.keyword" type="text" placeholder="Tên khóa học / lớp" class="w-3/4 border-gray-400 rounded-sm text-[13px] focus:ring-gray-500 italic placeholder-gray-400">
                            </div>
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

                    <div class="overflow-x-auto border border-gray-400">
                        <table class="min-w-full text-center text-[13px] divide-y divide-gray-400">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 w-12">STT</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400 text-left">Khóa học</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400">Lớp học</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400">Ngày cập nhật</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400">Điểm</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400">Kết quả</th>
                                    <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-400">Chứng chỉ</th>
                                    <th class="px-4 py-3 font-bold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-400">
                                <tr v-if="results.data.length === 0">
                                    <td colspan="8" class="px-4 py-8 text-gray-500 italic text-center">Chưa có kết quả học tập nào.</td>
                                </tr>
                                <tr v-for="(item, index) in results.data" :key="item.id" class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-700">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800 text-left font-medium">{{ item.course }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-700">{{ item.class }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-700">{{ item.date }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-800 font-bold">{{ item.score }}</td>
                                    <td class="px-4 py-3 border-r border-gray-400 font-bold" 
                                        :class="{'text-green-600': item.status === 'ĐẠT', 'text-yellow-600': item.status === 'CHỜ CHẤM', 'text-red-600': item.status === 'KHÔNG ĐẠT'}">
                                        {{ item.status }}
                                    </td>
                                    <td class="px-4 py-3 border-r border-gray-400 text-gray-700">{{ item.cert }}</td>
                                    <td class="px-4 py-3">
                                        <button @click="handleAction(item)" class="text-[#0ea5e9] hover:underline font-bold text-[13px] uppercase">
                                            {{ item.actionText }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-center items-center gap-2 text-sm font-medium" v-if="results.links && results.links.length > 3">
                        <Component v-for="(link, index) in results.links" :key="index"
                                   :is="link.url ? 'Link' : 'span'" 
                                   :href="link.url" 
                                   v-html="link.label" 
                                   class="w-8 h-8 rounded flex items-center justify-center transition-colors" 
                                   :class="{
                                       'bg-blue-100 text-blue-700 font-bold': link.active, 
                                       'text-gray-400 cursor-not-allowed': !link.url,
                                       'text-gray-600 hover:bg-gray-100': link.url && !link.active
                                   }" />
                    </div>

                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="showModal = false" maxWidth="sm">
            <div class="p-8 bg-white border-2 border-gray-300 shadow-xl">
                
                <template v-if="modalType === 'cert'">
                    <h3 class="text-lg font-bold text-gray-800 uppercase mb-4 border-b border-gray-300 pb-2">CHỨNG CHỈ</h3>
                    <div class="text-[14px] text-gray-800 leading-relaxed mb-8">
                        <p class="font-bold mb-2 text-blue-700 text-base">{{ selectedRow?.course }}</p>
                        <p><span class="font-bold">Họ tên:</span> {{ $page.props.auth.user.name }}</p>
                        <p><span class="font-bold">Ngày cấp:</span> {{ selectedRow?.date }}</p>
                        <p class="font-bold mt-2">Mã chứng chỉ:</p>
                        <p class="text-gray-600 font-mono">CERT-{{ selectedRow?.class }}-{{ selectedRow?.id }}</p>
                    </div>
                    <div class="flex justify-center text-[#d97706] font-bold text-[14px] uppercase tracking-wide">
                        <button onclick="window.print()" class="hover:underline transition border border-orange-500 px-4 py-2 rounded">[ TẢI HOẶC IN PDF ]</button>
                    </div>
                </template>

                <template v-else-if="modalType === 'failed'">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-300 pb-2">Thông báo</h3>
                    <div class="text-[14px] text-gray-800 leading-relaxed mb-8">
                        <p class="mb-3"><span class="font-bold">Kết quả:</span> <span class="font-bold text-[#c93b42]">KHÔNG ĐẠT</span></p>
                        <p>Bạn chưa đạt điều kiện cấp chứng chỉ cho khóa <strong class="text-gray-900">{{ selectedRow?.course }}</strong>.</p>
                    </div>
                    <div class="flex justify-end text-blue-700 font-bold text-[13px] uppercase tracking-wide">
                        <Link :href="route('employee.courses.index')" class="hover:underline transition">[ ĐĂNG KÝ HỌC LẠI ]</Link>
                    </div>
                </template>

                <template v-else-if="modalType === 'pending'">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-300 pb-2">Thông báo</h3>
                    <div class="text-[14px] text-gray-800 leading-relaxed mb-4">
                        <p class="mb-3"><span class="font-bold">Trạng thái:</span> <span class="font-bold text-[#d97706]">Chờ đánh giá</span></p>
                        <p>Bài thi cuối khóa của bạn đã được gửi đi. Kết quả sẽ được cập nhật sau khi giảng viên chấm điểm.</p>
                    </div>
                </template>

            </div>
        </Modal>

    </AuthenticatedLayout>
</template>