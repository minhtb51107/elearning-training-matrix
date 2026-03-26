<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

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
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 p-6 sm:p-8">
                    
                    <div class="mb-8 border-b border-gray-100 pb-5">
                        <h2 class="text-xl font-bold text-gray-900">Khám phá Khóa học</h2>
                        <p class="text-sm text-gray-500 mt-1">Tìm kiếm và đăng ký các chương trình đào tạo phù hợp với bạn.</p>
                    </div>

                    <div class="mb-10 bg-gray-50/80 p-5 rounded-xl border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:w-3/4">
                            <div>
                                <label class="block text-[13px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Trạng thái</label>
                                <select v-model="searchForm.type" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm text-gray-700">
                                    <option value="Tất cả">Tất cả phân loại</option>
                                    <option value="Bắt buộc">Khóa học Bắt buộc</option>
                                    <option value="Tự chọn">Khóa học Tự chọn</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[13px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Thời gian học</label>
                                <input v-model="searchForm.time" type="text" placeholder="Tính năng đang cập nhật..." disabled class="w-full bg-gray-100 border-gray-200 rounded-lg text-sm text-gray-400 cursor-not-allowed shadow-sm">
                            </div>
                            <div>
                                <label class="block text-[13px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tìm kiếm</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <MagnifyingGlassIcon class="w-4 h-4 text-gray-400" />
                                    </div>
                                    <input v-model="searchForm.keyword" type="text" placeholder="Nhập tên khóa học..." class="pl-9 w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-if="courses.data.length === 0" class="col-span-full text-center py-12 bg-gray-50/50 rounded-xl border border-dashed border-gray-200">
                            <p class="text-gray-500 font-medium">Chưa có khóa học nào được phân bổ cho bộ phận của bạn hoặc phù hợp với bộ lọc.</p>
                        </div>

                        <div v-for="course in courses.data" :key="course.id" 
                             class="group border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:border-blue-200 transition-all bg-white flex flex-col cursor-pointer" 
                             @click="router.get(route('employee.courses.show', course.id))">
                            
                            <div class="h-40 w-full relative overflow-hidden bg-gradient-to-br from-blue-500 to-purple-600">
                                <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors"></div>
                                <img v-if="course.image" :src="course.image" class="w-full h-full object-cover mix-blend-overlay" alt="Course Cover">
                                <div class="absolute top-3 left-3">
                                    <span :class="['px-2.5 py-1 rounded-md text-xs font-bold shadow-sm', course.type === 'Bắt buộc' ? 'bg-red-500 text-white' : 'bg-white/90 text-gray-800']">
                                        {{ course.type }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2 leading-tight group-hover:text-blue-600 transition-colors" :title="course.title">
                                    {{ course.title }}
                                </h3>
                                
                                <div class="text-sm text-gray-500 mb-5 flex-1">
                                    <p class="line-clamp-2 mb-2">{{ course.description || 'Chưa có mô tả chi tiết cho khóa học này.' }}</p>
                                    <p v-if="course.date" class="text-xs font-medium bg-gray-100 inline-block px-2 py-1 rounded text-gray-600 mt-1">📅 {{ course.date }}</p>
                                </div>
                                
                                <div class="flex justify-end mt-auto pt-4 border-t border-gray-100">
                                    <button class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1 group-hover:gap-2 transition-all">
                                        Xem chi tiết <span aria-hidden="true">&rarr;</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-between items-center text-sm text-gray-600" v-if="courses.links.length > 3">
                        <div>Trang hiện tại</div>
                        <div class="flex gap-1">
                            <Component v-for="(link, index) in courses.links" :key="index"
                                       :is="link.url ? 'Link' : 'span'" 
                                       :href="link.url" 
                                       v-html="link.label" 
                                       class="px-3 py-1.5 border rounded-md transition-colors" 
                                       :class="{
                                           'bg-blue-600 text-white border-blue-600 font-medium shadow-sm': link.active, 
                                           'text-gray-400 border-gray-200 bg-gray-50 cursor-not-allowed': !link.url,
                                           'hover:bg-gray-50 text-gray-700 border-gray-300': link.url && !link.active
                                       }" />
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>