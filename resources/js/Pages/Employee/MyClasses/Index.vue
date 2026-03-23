<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    classes: Object,
    filters: Object
});

// FORM BỘ LỌC
const searchForm = ref({
    status: props.filters?.status || 'Tất cả',
    time: '',
    keyword: props.filters?.keyword || ''
});

// Tự động tìm kiếm sau 300ms khi người dùng ngừng gõ
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
    <Head title="Danh sách lớp học" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Danh sách lớp học</h2>

                    <div class="mb-8">
                        <label class="block text-base font-bold text-gray-800 mb-4">Bộ lọc:</label>
                        <div class="grid grid-cols-3 gap-6 w-3/4">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Trạng thái:</label>
                                <select v-model="searchForm.status" class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500 text-gray-800">
                                    <option>Tất cả</option>
                                    <option>Đang học</option>
                                    <option>Hoàn thành</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Thời gian:</label>
                                <input v-model="searchForm.time" type="text" placeholder="Tính năng đang cập nhật..." disabled class="w-full border-gray-400 bg-gray-100 rounded-sm text-sm focus:ring-blue-500 text-gray-400 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Từ khóa (Enter để tìm):</label>
                                <input v-model="searchForm.keyword" type="text" placeholder="Nhập tên khóa học..." class="w-full border-gray-400 rounded-sm text-sm focus:ring-blue-500 placeholder-gray-400">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-8">
                        <div v-if="classes.data.length === 0" class="col-span-3 text-center py-10 text-gray-500 italic">
                            Bạn chưa được phân bổ vào lớp học nào phù hợp với tìm kiếm.
                        </div>

                        <div v-for="cls in classes.data" :key="cls.id" class="border border-gray-300 rounded overflow-hidden shadow-sm hover:shadow-md transition bg-white flex flex-col">
                            <div class="h-40 bg-gray-200 w-full overflow-hidden flex items-center justify-center relative">
                                <div class="absolute inset-0 bg-blue-500 opacity-10"></div>
                                <img src="https://placehold.co/600x300/0ea5e9/white?text=E-LEARNING" class="w-full h-full object-cover">
                            </div>
                            
                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-base font-bold text-gray-800 uppercase mb-2 line-clamp-2 leading-tight" :title="cls.title">{{ cls.title }}</h3>
                                
                                <div class="text-[13px] text-gray-600 mb-4 flex-1">
                                    <p v-if="cls.description" class="italic mb-1 line-clamp-1">{{ cls.description }}</p>
                                    <p v-if="cls.date" class="mb-1">Date: {{ cls.date }}</p>
                                    <p>Trạng thái: <span class="font-bold">{{ cls.statusText }}</span></p>
                                    
                                    <div v-if="cls.progress !== null" class="mt-3 flex items-center gap-3">
                                        <div class="flex-1 bg-gray-200 h-4 border border-gray-300 relative">
                                            <div class="h-full bg-blue-400" :style="{ width: cls.progress + '%' }"></div>
                                            <span class="absolute inset-0 flex items-center justify-center text-[10px] font-bold" :class="{'text-gray-800': !cls.isFailed, 'text-red-700': cls.isFailed}">
                                                {{ cls.progressText }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end mt-auto">
                                    <Link :href="route('employee.courses.show', cls.id)" class="border border-gray-400 text-gray-600 hover:text-white hover:bg-[#0ea5e9] hover:border-[#0ea5e9] text-xs font-bold px-3 py-1.5 rounded transition uppercase">
                                        {{ cls.btn }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-center items-center gap-2 text-sm text-[#0ea5e9] font-medium" v-if="classes.links.length > 3">
                        <Component v-for="link in classes.links" :key="link.label"
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