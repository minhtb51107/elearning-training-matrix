<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    courses: Object,
    filters: Object
});

// BIẾN CHO BỘ LỌC
const searchKeyword = ref(props.filters?.keyword || '');
const filterFromDate = ref(props.filters?.from_date || '');
const filterToDate = ref(props.filters?.to_date || '');
const filterFormat = ref(props.filters?.format || 'all');
const filterStatus = ref(props.filters?.status || 'all');

const doSearch = () => {
    router.get(route('department.courses.index'), {
        keyword: searchKeyword.value,
        from_date: filterFromDate.value,
        to_date: filterToDate.value,
        format: filterFormat.value,
        status: filterStatus.value
    }, { preserveState: true, replace: true });
};

// HELPER: Format Ngày tháng (từ DB ra DD/MM/YYYY)
const formatDate = (dateString) => {
    if (!dateString) return '--';
    const d = new Date(dateString);
    return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
};

// HELPER: Dịch Trạng thái từ Database ra Tiếng Việt
const getStatusText = (status) => {
    const map = {
        'published': 'Đang mở',
        'draft': 'Đang triển khai',
        'archived': 'Kết thúc'
    };
    return map[status] || status;
};

// HELPER: Đổ màu full ô theo đúng mockup
const getStatusBgClass = (dbStatus) => {
    const status = getStatusText(dbStatus);
    if (status === 'Đang mở') return 'bg-[#b7eb8f] text-gray-900'; // Xanh lá nhạt
    if (status === 'Đang triển khai') return 'bg-[#fffb8f] text-gray-900'; // Vàng nhạt
    if (status === 'Kết thúc') return 'bg-[#ff4d4f] text-white'; // Đỏ
    return 'bg-gray-100 text-gray-800';
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
                                <label class="block text-sm text-gray-600 mb-1">Khóa học (Enter để tìm):</label>
                                <input v-model="searchKeyword" @keyup.enter="doSearch" type="text" class="w-full border-gray-400 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Nhập tên / Mã khóa học..." />
                            </div>
                            <div class="w-36">
                                <label class="block text-sm text-gray-600 mb-1">Từ ngày:</label>
                                <input v-model="filterFromDate" @change="doSearch" type="date" class="w-full border-gray-400 rounded-md text-sm text-gray-600 focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <div class="w-36">
                                <label class="block text-sm text-gray-600 mb-1">Đến ngày:</label>
                                <input v-model="filterToDate" @change="doSearch" type="date" class="w-full border-gray-400 rounded-md text-sm text-gray-600 focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                            <div class="w-36">
                                <label class="block text-sm text-gray-600 mb-1">Hình thức:</label>
                                <select v-model="filterFormat" @change="doSearch" class="w-full border-gray-400 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="all">Tất cả</option>
                                    <option value="offline">Offline</option>
                                    <option value="online">Online</option>
                                    <option value="blended">Blended</option>
                                </select>
                            </div>
                            <div class="w-40">
                                <label class="block text-sm text-gray-600 mb-1">Trạng thái:</label>
                                <select v-model="filterStatus" @change="doSearch" class="w-full border-gray-400 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="all">Tất cả</option>
                                    <option value="published">Đang mở</option>
                                    <option value="draft">Đang triển khai</option>
                                    <option value="archived">Kết thúc</option>
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
                                <tr v-if="courses.data.length === 0">
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500 italic">Không tìm thấy khóa học nào phù hợp.</td>
                                </tr>
                                
                                <tr v-for="course in courses.data" :key="course.id" class="hover:bg-gray-50 transition cursor-pointer" @click="router.get(route('department.courses.show', course.id))">
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-700">{{ course.code }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-800 font-medium">{{ course.name }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-600">{{ formatDate(course.created_at) }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-700 capitalize">{{ course.format }}</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-700">--</td>
                                    <td class="px-4 py-3 text-sm border-r border-gray-400 text-gray-700 text-center font-bold">{{ course.course_classes_count || 0 }}</td>
                                    
                                    <td :class="['px-4 py-3 text-[13px] font-bold border-l border-gray-400 text-center uppercase tracking-wide', getStatusBgClass(course.status)]">
                                        {{ getStatusText(course.status) }}
                                    </td>
                                </tr>
                                
                                <tr v-for="i in Math.max(0, 3 - courses.data.length)" :key="'empty-'+i">
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5 border-r border-gray-400"></td>
                                    <td class="px-4 py-5"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-center items-center gap-2 text-sm text-[#0ea5e9] font-medium" v-if="courses.links.length > 3">
                        <Component v-for="link in courses.links" :key="link.label"
                                   :is="link.url ? 'a' : 'span'" 
                                   :href="link.url" 
                                   v-html="link.label" 
                                   class="px-3 py-1 border border-gray-300 rounded text-sm transition" 
                                   :class="{
                                       'bg-[#0ea5e9] text-white font-bold': link.active, 
                                       'text-gray-400 cursor-not-allowed': !link.url,
                                       'hover:bg-gray-100': link.url && !link.active
                                   }" />
                    </div>

                    <div class="mt-8 flex justify-center">
                        <Link :href="route('department.requests.create')" class="text-[#d97706] hover:text-orange-700 font-bold text-[15px] uppercase tracking-wide transition">
                            [ + GỬI YÊU CẦU ĐÀO TẠO MỚI ]
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>