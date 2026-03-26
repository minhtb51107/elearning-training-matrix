<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeftIcon, InformationCircleIcon, AcademicCapIcon, DocumentTextIcon, LinkIcon, ArrowDownTrayIcon, UsersIcon } from '@heroicons/vue/20/solid';

// Hứng dữ liệu thật từ Controller truyền sang
const props = defineProps({
    courseInfo: Object,
    classes: Array,
    materials: Array
});
</script>

<template>
    <Head :title="'Chi tiết khóa học: ' + courseInfo.name" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('system.courses.index')" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                <ArrowLeftIcon class="w-4 h-4" />
                Quay lại danh sách Khóa học
            </Link>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    
                    <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 shadow-sm border border-blue-200 shrink-0">
                                <AcademicCapIcon class="w-8 h-8" />
                            </div>
                            <div class="flex-1 mt-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-md text-xs font-bold border border-blue-200">{{ courseInfo.code }}</span>
                                    <span class="bg-purple-100 text-purple-700 px-2.5 py-0.5 rounded-md text-xs font-bold border border-purple-200">{{ courseInfo.format }}</span>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-900">{{ courseInfo.name }}</h2>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        
                        <div class="mb-10">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-gray-100 pb-2">
                                <InformationCircleIcon class="w-4 h-4 text-blue-500" />
                                Thông tin triển khai
                            </h3>
                            
                            <div class="bg-gray-50/50 border border-gray-100 p-6 rounded-xl">
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-y-6 gap-x-8 text-sm">
                                    <div>
                                        <p class="text-gray-500 font-medium mb-1">Mã khóa học</p>
                                        <p class="font-bold text-gray-900">{{ courseInfo.code }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 font-medium mb-1">Hình thức</p>
                                        <p class="font-bold text-gray-900">{{ courseInfo.format }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 font-medium mb-1">Thời lượng dự kiến</p>
                                        <p class="font-bold text-gray-900">{{ courseInfo.duration }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 font-medium mb-1">Phạm vi đào tạo</p>
                                        <p class="font-bold text-gray-900">{{ courseInfo.scope }}</p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <p class="text-gray-500 font-medium mb-1">Đối tượng tham gia</p>
                                        <p class="font-bold text-gray-900">{{ courseInfo.target_audience }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            
                            <div class="lg:col-span-2">
                                <div class="flex justify-between items-end mb-4 border-b border-gray-100 pb-2">
                                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Danh sách lớp học</h3>
                                    </div>
                                
                                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mã lớp</th>
                                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tên lớp</th>
                                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Giảng viên</th>
                                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày học</th>
                                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Đã ĐK</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-if="classes.length === 0">
                                                <td colspan="5" class="px-5 py-8 text-center text-gray-500 italic bg-gray-50/30">
                                                    Chưa có lớp học nào được tạo cho khóa học này.
                                                </td>
                                            </tr>
                                            <tr v-for="(cls, index) in classes" :key="index" class="hover:bg-blue-50/50 transition-colors duration-150">
                                                <td class="px-5 py-3 font-semibold text-gray-900">{{ cls.code }}</td>
                                                <td class="px-5 py-3 font-medium text-gray-800">{{ cls.name }}</td>
                                                <td class="px-5 py-3 text-gray-600">{{ cls.instructor }}</td>
                                                <td class="px-5 py-3 text-gray-600">{{ cls.time }}</td>
                                                <td class="px-5 py-3 text-center">
                                                    <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-1 rounded-md font-bold text-xs border border-blue-100">
                                                        <UsersIcon class="w-3.5 h-3.5" /> {{ cls.capacity }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Tài liệu khóa học</h3>
                                
                                <div class="space-y-3">
                                    <div v-if="materials.length === 0" class="text-sm text-gray-400 italic text-center p-6 bg-gray-50/80 rounded-xl border border-gray-100 border-dashed">
                                        Chưa có tài liệu đính kèm.
                                    </div>
                                    
                                    <a v-for="(mat, index) in materials" :key="index" 
                                       :href="mat.url" 
                                       target="_blank" 
                                       class="group block bg-white p-3.5 rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all">
                                        <div class="flex items-start gap-3">
                                            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors shadow-sm">
                                                <DocumentTextIcon v-if="mat.type === 'file'" class="w-5 h-5" />
                                                <LinkIcon v-else class="w-5 h-5" />
                                            </div>
                                            <div class="flex-1 min-w-0 pt-0.5">
                                                <p class="text-sm font-bold text-gray-900 truncate group-hover:text-blue-700 transition-colors">{{ mat.name }}</p>
                                                <p class="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                                    <ArrowDownTrayIcon v-if="mat.type === 'file'" class="w-3 h-3" />
                                                    {{ mat.type === 'file' ? 'Tải xuống File' : 'Nhấp để mở Link' }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>