<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

// MOCK DATA TỪ THIẾT KẾ
const allClasses = ref([
    { id: 1, code: 'CLS-2026-SA-01', name: 'Sales nâng cao - L1', course: 'Kỹ năng bán hàng', instructor: 'Nguyễn Văn A', time: '20-22/01/2026', students: 30, status: 'Mở đăng ký' },
    { id: 2, code: 'CLS-2026-IT-02', name: 'DevOps cơ bản - L2', course: 'DevOps cơ bản', instructor: 'Hồ Thị B', time: '05-07/02/2026', students: 25, status: 'Đang học' },
    { id: 3, code: 'CLS-2025-HR-01', name: 'Tuyển dụng hiệu quả - L1', course: 'Tuyển dụng hiệu quả', instructor: 'Nguyễn Hoàng C', time: '10-12/12/2025', students: 28, status: 'Kết thúc' },
    { id: 4, code: 'CLS-2025-HR-02', name: 'Tuyển dụng hiệu quả - L2', course: 'Tuyển dụng hiệu quả', instructor: 'Nguyễn Hoàng C', time: '10-12/12/2025', students: 28, status: 'Nháp' },
]);

const activeTab = ref('Tất Cả');

const tabs = computed(() => {
    return [
        { name: 'Tất Cả', count: allClasses.value.length },
        { name: 'Nháp', count: allClasses.value.filter(c => c.status === 'Nháp').length },
        { name: 'Mở đăng ký', count: allClasses.value.filter(c => c.status === 'Mở đăng ký').length },
        { name: 'Đang học', count: allClasses.value.filter(c => c.status === 'Đang học').length },
        { name: 'Kết thúc', count: allClasses.value.filter(c => c.status === 'Kết thúc').length },
    ];
});

const filteredClasses = computed(() => {
    if (activeTab.value === 'Tất Cả') return allClasses.value;
    return allClasses.value.filter(c => c.status === activeTab.value);
});
</script>

<template>
    <Head title="Quản lý lớp học" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-8">Quản lý lớp học</h2>

                    <div class="flex items-center gap-4 mb-8 text-[15px]">
                        <template v-for="(tab, index) in tabs" :key="tab.name">
                            <button 
                                @click="activeTab = tab.name"
                                :class="['transition', activeTab === tab.name ? 'text-gray-900 font-extrabold border-b-[3px] border-gray-900 pb-0.5' : 'text-gray-500 hover:text-gray-800 font-medium']"
                            >
                                {{ tab.name }} ({{ tab.count }})
                            </button>
                            <span v-if="index < tabs.length - 1" class="text-gray-300 font-bold">|</span>
                        </template>
                    </div>

                    <div class="mb-8 border-t border-gray-200 pt-6">
                        <label class="block text-base font-bold text-gray-800 mb-4">Bộ lọc:</label>
                        <div class="grid grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Khóa học:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-600">
                                    <option>Yêu cầu đào tạo</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Phạm vi:</label>
                                <select class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-600">
                                    <option>Phòng kinh doanh</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Thời gian:</label>
                                <input type="text" placeholder="Chọn thời gian..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500 text-gray-400">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Từ khóa:</label>
                                <input type="text" placeholder="Nhập và chọn" class="w-full border-gray-400 rounded-sm text-sm focus:ring-gray-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center mb-6">
                        <Link :href="route('system.classes.create')" class="text-[#d97706] hover:text-orange-700 font-bold text-[15px] uppercase tracking-wide">
                            [ + TẠO LỚP HỌC MỚI ]
                        </Link>
                    </div>

                    <h3 class="text-base font-bold text-gray-800 mb-3">Danh sách lớp học:</h3>
                    <div class="overflow-x-auto border border-gray-300">
                        <table class="min-w-full divide-y divide-gray-300 text-center text-[13px]">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Mã lớp học</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Tên lớp học</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Khóa học</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Giảng viên</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Thời gian</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Số lượng</th>
                                    <th class="px-3 py-3 font-bold text-gray-900">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-300">
                                <tr v-if="filteredClasses.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 italic">Không có dữ liệu.</td>
                                </tr>
                                <tr v-for="cls in filteredClasses" :key="cls.id" class="hover:bg-gray-50 transition cursor-pointer" @click="router.get(route('system.classes.show', cls.id))">
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ cls.code }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-800 font-medium">{{ cls.name }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ cls.course }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ cls.instructor }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ cls.time }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ cls.students }}</td>
                                    <td class="px-3 py-3 font-bold text-gray-800">{{ cls.status }}</td>
                                </tr>
                                <tr v-for="i in 2" :key="'empty-'+i">
                                    <td class="px-3 py-4 border-r border-gray-300"></td>
                                    <td class="px-3 py-4 border-r border-gray-300"></td>
                                    <td class="px-3 py-4 border-r border-gray-300"></td>
                                    <td class="px-3 py-4 border-r border-gray-300"></td>
                                    <td class="px-3 py-4 border-r border-gray-300"></td>
                                    <td class="px-3 py-4 border-r border-gray-300"></td>
                                    <td class="px-3 py-4"></td>
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