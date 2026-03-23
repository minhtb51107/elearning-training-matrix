<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    courses: Object,
    filters: Object
});

const searchForm = ref({
    type: props.filters?.type || 'Tất cả',
    time: '',
    keyword: props.filters?.keyword || ''
});

let searchTimeout = null;
watch(searchForm, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('employee.courses.index'), newValue, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }, 300);
}, { deep: true });
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
                                <select v-model="searchForm.type" class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500 text-gray-800">
                                    <option value="Tất cả">Tất cả</option>
                                    <option value="Bắt buộc">Bắt buộc</option>
                                    <option value="Tự chọn">Tự chọn</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Thời gian:</label>
                                <input v-model="searchForm.time" type="text" placeholder="Tính năng đang cập nhật..." disabled class="w-full border-gray-400 bg-gray-100 rounded-sm text-sm text-gray-400 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Từ khóa (Enter để tìm):</label>
                                <input v-model="searchForm.keyword" type="text" placeholder="Nhập tên khóa học..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500 placeholder-gray-400">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-8">
                        <div v-if="courses.data.length === 0" class="col-span-3 text-center py-10 text-gray-500 italic">
                            Chưa có khóa học nào được phân bổ cho bộ phận của bạn.
                        </div>

                        <div v-for="course in courses.data" :key="course.id" class="border border-gray-300 rounded overflow-hidden shadow-sm hover:shadow-md transition bg-white flex flex-col cursor-pointer" @click="router.get(route('employee.courses.show', course.id))">
                            
                            <div class="h-40 bg-gray-200 w-full overflow-hidden relative">
                                <div class="absolute inset-0 bg-blue-500 opacity-20"></div>
                                <img :src="course.image" class="w-full h-full object-cover" alt="Course Cover">
                            </div>
                            
                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-base font-bold text-gray-800 uppercase mb-2 line-clamp-2 leading-tight" :title="course.title">{{ course.title }}</h3>
                                
                                <div class="text-[13px] text-gray-600 mb-4 flex-1">
                                    <p class="italic line-clamp-2 mb-1">{{ course.description }}</p>
                                    <p v-if="course.date">Date: {{ course.date }}</p>
                                    <p>Trạng thái: <span class="font-medium" :class="course.type === 'Bắt buộc' ? 'text-red-600' : 'text-gray-800'">{{ course.type }}</span></p>
                                </div>
                                
                                <div class="flex justify-end mt-auto">
                                    <button class="border border-gray-400 text-gray-600 hover:text-white hover:bg-[#0ea5e9] hover:border-[#0ea5e9] text-xs font-bold px-3 py-1.5 rounded transition uppercase">
                                        Xem chi tiết
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-center items-center gap-2 text-sm text-[#0ea5e9] font-medium" v-if="courses.links.length > 3">
                        <Component v-for="link in courses.links" :key="link.label"
                                   :is="link.url ? 'Link' : 'span'" 
                                   :href="link.url" 
                                   v-html="link.label" 
                                   class="w-7 h-7 rounded flex items-center justify-center transition" 
                                   :class="{
                                       'bg-blue-100 font-bold': link.active, 
                                       'text-gray-400 cursor-not-allowed': !link.url,
                                       'hover:bg-gray-100 text-gray-600': link.url && !link.active
                                   }" />
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>