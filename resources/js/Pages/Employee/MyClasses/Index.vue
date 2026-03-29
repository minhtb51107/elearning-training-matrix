<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MagnifyingGlassIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    classes: Object,
    filters: Object
});

const searchForm = ref({
    status: props.filters?.status || 'Tất cả',
    time: '',
    keyword: props.filters?.keyword || ''
});

let searchTimeout = null;
watch(searchForm, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('employee.my-classes'), newValue, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }, 300);
}, { deep: true });
</script>

<template>
    <Head title="Lớp học của tôi" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 sm:p-8 border border-gray-200">
                    
                    <div class="mb-8 border-b border-gray-100 pb-5">
                        <h2 class="text-xl font-bold text-gray-900">Lớp học của tôi</h2>
                        <p class="text-sm text-gray-500 mt-1">Danh sách các chương trình đào tạo bạn đã đăng ký hoặc được chỉ định.</p>
                    </div>

                    <div class="mb-10 bg-gray-50/80 p-5 rounded-xl border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:w-3/4">
                            <div>
                                <label class="block text-[13px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Trạng thái Lớp</label>
                                <select v-model="searchForm.status" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm text-gray-700">
                                    <option value="Tất cả">Tất cả trạng thái</option>
                                    <option value="Đang học">Đang học / Mở đăng ký</option>
                                    <option value="Hoàn thành">Đã hoàn thành</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[13px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Thời gian diễn ra</label>
                                <input v-model="searchForm.time" type="text" placeholder="Tính năng đang cập nhật..." disabled class="w-full bg-gray-100 border-gray-200 rounded-lg text-sm text-gray-400 cursor-not-allowed shadow-sm">
                            </div>
                            <div>
                                <label class="block text-[13px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Tìm kiếm (Tên lớp)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <MagnifyingGlassIcon class="w-4 h-4 text-gray-400" />
                                    </div>
                                    <input v-model="searchForm.keyword" type="text" placeholder="Nhập mã hoặc tên lớp..." class="pl-9 w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-if="classes.data.length === 0" class="col-span-full text-center py-12 bg-gray-50/50 rounded-xl border border-dashed border-gray-200">
                            <p class="text-gray-500 font-medium">Bạn chưa đăng ký tham gia lớp học nào phù hợp với điều kiện tìm kiếm.</p>
                            <Link :href="route('employee.courses.index')" class="mt-3 inline-block text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                Khám phá Khóa học &rarr;
                            </Link>
                        </div>

                        <div v-for="cls in classes.data" :key="cls.id" class="border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:border-blue-200 transition-all bg-white flex flex-col group relative">
                            
                            <div v-if="cls.is_mandatory" class="absolute top-3 right-3 z-10 bg-red-600 text-white text-[10px] font-black px-2 py-1 rounded shadow-sm border border-red-800 flex items-center gap-1 uppercase tracking-wider">
                                <ExclamationCircleIcon class="w-3.5 h-3.5" /> Bắt buộc
                            </div>

                            <div class="h-32 w-full relative overflow-hidden bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center p-4">
                                <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors"></div>
                                <h3 class="text-xl font-black text-white text-center drop-shadow-md line-clamp-2 mix-blend-overlay uppercase tracking-wider">{{ cls.title }}</h3>
                            </div>
                            
                            <div class="p-5 flex flex-col flex-1">
                                <p class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 line-clamp-1" :title="cls.course_name">{{ cls.course_name }}</p>
                                <h4 class="text-base font-bold text-gray-900 mb-3 line-clamp-2 leading-tight">{{ cls.description || 'Chưa có mô tả' }}</h4>
                                
                                <div class="text-sm text-gray-500 mb-4 flex-1 space-y-1">
                                    <p class="flex items-center gap-2">
                                        <span class="w-4 h-4 text-center">📅</span> 
                                        <span>{{ cls.date }}</span>
                                    </p>
                                    <p class="flex items-center gap-2">
                                        <span class="w-4 h-4 text-center">🏷️</span> 
                                        <span class="font-medium" :class="cls.isFailed ? 'text-red-600 font-bold' : (cls.statusText.includes('Đã đăng ký') ? 'text-amber-600' : (cls.statusText.includes('Hoàn thành') ? 'text-green-600' : 'text-blue-600'))">
                                            {{ cls.statusText }}
                                        </span>
                                    </p>
                                    <p v-if="cls.is_mandatory && cls.deadline" class="flex items-center gap-2 text-red-600 font-bold mt-2 bg-red-50 p-1.5 rounded-md border border-red-100">
                                        <span class="w-4 h-4 text-center">⏰</span> Hạn chót: {{ cls.deadline }}
                                    </p>
                                </div>
                                
                                <div v-if="cls.progress !== null" class="mb-5">
                                    <div class="flex justify-between text-xs font-semibold mb-1">
                                        <span class="text-gray-500">Tiến độ</span>
                                        <span :class="cls.isFailed ? 'text-red-600' : 'text-blue-600'">{{ cls.progressText }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                        <div class="h-2.5 rounded-full transition-all duration-500" 
                                             :class="cls.isFailed ? 'bg-red-500' : 'bg-blue-500'" 
                                             :style="{ width: cls.progress + '%' }"></div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end mt-auto pt-4 border-t border-gray-100 items-center">
                                    <Link :href="route('employee.my-classes.show', cls.id)" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1 group-hover:gap-2 transition-all uppercase tracking-wide">
                                        {{ cls.btn }} <span aria-hidden="true">&rarr;</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-between items-center text-sm text-gray-600" v-if="classes.links.length > 3">
                        <div>Trang hiện tại</div>
                        <div class="flex gap-1">
                            <Component v-for="(link, index) in classes.links" :key="index"
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