<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { 
    ChevronLeftIcon, 
    CalendarDaysIcon, 
    UserIcon, 
    ArrowRightEndOnRectangleIcon,
    VideoCameraIcon,
    DocumentTextIcon,
    PlayCircleIcon
} from '@heroicons/vue/20/solid';

const props = defineProps({
    course: Object
});

const activeTab = ref('Giới thiệu');
const showConfirmModal = ref(false);
const selectedClass = ref(null);

const openConfirmModal = (cls) => {
    selectedClass.value = cls;
    showConfirmModal.value = true;
};
const closeConfirmModal = () => showConfirmModal.value = false;

const confirmEnrollment = () => {
    if (!selectedClass.value) return;
    
    router.post(route('employee.classes.enroll', selectedClass.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            closeConfirmModal();
        }
    });
};
</script>

<template>
    <Head :title="course.name" />

    <AuthenticatedLayout>
        
        <template #header>
            <Link :href="route('employee.courses.index')" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                <ChevronLeftIcon class="w-4 h-4" />
                Quay lại danh sách
            </Link>
        </template>

        <div class="py-8">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    
                    <div class="px-8 py-8 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-md text-xs font-bold border border-blue-200">Khóa học Nội bộ</span>
                        </div>
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight">{{ course.name }}</h2>
                    </div>

                    <div class="px-8 border-b border-gray-200 bg-white">
                        <nav class="-mb-px flex space-x-8">
                            <button @click="activeTab = 'Giới thiệu'" :class="['whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors', activeTab === 'Giới thiệu' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">Giới thiệu & Lịch học</button>
                            <button @click="activeTab = 'Nội dung'" :class="['whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors', activeTab === 'Nội dung' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">Nội dung chi tiết</button>
                            <button @click="activeTab = 'Đánh giá'" :class="['whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors', activeTab === 'Đánh giá' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">Đánh giá (4.2★)</button>
                        </nav>
                    </div>

                    <div class="p-8">
                        <div v-if="activeTab === 'Giới thiệu'" class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                            
                            <div class="lg:col-span-2">
                                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Về khóa học này</h3>
                                <div class="prose prose-sm prose-blue text-gray-600 max-w-none">
                                    <p v-for="(item, idx) in course.overview" :key="idx" class="mb-3 leading-relaxed">{{ item }}</p>
                                    <p v-if="!course.overview || course.overview.length === 0" class="italic text-gray-400">Đang cập nhật nội dung giới thiệu...</p>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Lớp học đang mở</h3>
                                <div class="space-y-4">
                                    <div v-if="!course.classes || course.classes.length === 0" class="text-sm text-gray-500 italic p-4 border border-gray-200 border-dashed rounded-lg bg-gray-50 text-center">
                                        Chưa có lịch mở lớp mới. Vui lòng quay lại sau.
                                    </div>

                                    <div v-for="cls in course.classes" :key="cls.id" class="border border-gray-200 p-5 rounded-xl relative shadow-sm hover:border-blue-300 transition-colors bg-white group">
                                        <h4 class="text-base font-bold text-gray-900 mb-3">{{ cls.name }}</h4>
                                        <div class="space-y-2 mb-5">
                                            <p class="text-sm text-gray-600 flex items-start gap-2"><CalendarDaysIcon class="w-4 h-4 text-gray-400 mt-0.5 shrink-0"/> <span>{{ cls.time }}</span></p>
                                            <p class="text-sm text-gray-600 flex items-center gap-2"><UserIcon class="w-4 h-4 text-gray-400 shrink-0"/> <span>GV: {{ cls.instructor }}</span></p>
                                        </div>
                                        
                                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                            <div>
                                                <p v-if="!cls.isFull" class="text-xs font-medium text-gray-500">Còn <span class="text-green-600 font-bold">{{ cls.slot }}</span> chỗ</p>
                                                <p v-else class="text-xs font-bold text-red-600">Đã hết chỗ</p>
                                            </div>
                                            
                                            <button v-if="cls.isEnrolled" disabled class="bg-gray-100 text-gray-500 font-semibold py-1.5 px-4 rounded-lg text-xs cursor-not-allowed border border-gray-200">
                                                Đã đăng ký
                                            </button>
                                            <button v-else-if="!cls.isFull" @click="openConfirmModal(cls)" class="bg-blue-600 text-white font-semibold py-1.5 px-4 rounded-lg text-xs hover:bg-blue-700 transition shadow-sm shadow-blue-600/20 group-hover:scale-105">
                                                Đăng ký ngay
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'Nội dung'" class="max-w-3xl">
                            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-6">Chương trình đào tạo ({{ course.content?.length || 0 }} bài học)</h3>
                            
                            <ul v-if="course.content && course.content.length > 0" class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden bg-gray-50/30">
                                <li v-for="(item, idx) in course.content" :key="idx" class="hover:bg-white transition-colors group">
                                    <a :href="item.url" target="_blank" class="px-6 py-4 flex justify-between items-center text-sm w-full cursor-pointer">
                                        <div class="flex items-center gap-4">
                                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors shadow-sm">
                                                <PlayCircleIcon v-if="item.type === 'youtube'" class="w-5 h-5" />
                                                <VideoCameraIcon v-else-if="item.type === 'video_upload'" class="w-5 h-5" />
                                                <DocumentTextIcon v-else class="w-5 h-5" />
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors">{{ item.title }}</p>
                                                <p class="text-xs text-gray-500 mt-0.5">
                                                    {{ item.type === 'youtube' ? 'Video Bài giảng' : (item.type === 'video_upload' ? 'Video Tải lên' : 'Tài liệu Slide PDF') }}
                                                </p>
                                            </div>
                                        </div>
                                        <span class="text-gray-500 bg-gray-100 px-3 py-1 rounded text-xs font-medium">{{ item.time }}</span>
                                    </a>
                                </li>
                            </ul>
                            
                            <div v-else class="text-center py-10 border border-gray-200 border-dashed rounded-xl bg-gray-50 mb-8">
                                <p class="text-sm text-gray-500 italic">Chưa có bài giảng nào được thêm vào khóa học này.</p>
                            </div>

                            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 mt-10">Tài liệu đính kèm (Tải về)</h3>
                            <div v-if="course.documents && course.documents.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <a v-for="doc in course.documents" :key="doc.id" :href="doc.url" target="_blank" 
                                   class="flex items-center gap-3 p-4 bg-gray-50 border border-gray-200 rounded-xl hover:border-blue-300 hover:bg-blue-50 transition-colors group cursor-pointer shadow-sm">
                                    <div class="p-2 bg-white rounded-lg shadow-sm text-blue-600 group-hover:scale-110 transition-transform">
                                        <DocumentTextIcon class="w-5 h-5" />
                                    </div>
                                    <span class="font-medium text-gray-700 group-hover:text-blue-700 line-clamp-1">{{ doc.title }}</span>
                                </a>
                            </div>
                            <div v-else class="text-sm text-gray-500 italic px-2">Khóa học này không có tài liệu đính kèm.</div>
                        </div>

                        <div v-if="activeTab === 'Đánh giá'" class="max-w-3xl">
                            <div class="flex items-center gap-6 mb-8 p-6 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="text-center">
                                    <p class="text-4xl font-black text-gray-900">4.2</p>
                                    <p class="text-yellow-400 text-lg tracking-widest mt-1">★★★★☆</p>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-base font-bold text-gray-900 mb-1">Nhận xét từ Học viên</h3>
                                    <p class="text-sm text-gray-500">Đánh giá thực tế từ những người đã hoàn thành khóa học.</p>
                                </div>
                                <button class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition shadow-sm">
                                    Viết đánh giá
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div class="p-5 border border-gray-100 rounded-xl bg-white shadow-sm">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">A</div>
                                            <div>
                                                <p class="font-semibold text-sm text-gray-900">Nguyễn A</p>
                                                <p class="text-yellow-400 text-xs tracking-widest">★★★★★</p>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-400">12/01/2026</p>
                                    </div>
                                    <p class="text-sm text-gray-600 leading-relaxed pl-10">"Nội dung dễ hiểu, giảng viên nhiệt tình hỗ trợ giải đáp thắc mắc. Rất đáng học cho người mới."</p>
                                </div>
                                
                                <div class="p-5 border border-gray-100 rounded-xl bg-white shadow-sm">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">B</div>
                                            <div>
                                                <p class="font-semibold text-sm text-gray-900">Trần B</p>
                                                <p class="text-yellow-400 text-xs tracking-widest">★★★★☆</p>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-400">10/01/2026</p>
                                    </div>
                                    <p class="text-sm text-gray-600 leading-relaxed pl-10">"Slide bài giảng đẹp, nhưng bài Quiz cuối khóa hơi khó so với thời lượng học."</p>
                                </div>
                            </div>
                            
                            <div class="mt-6 text-center">
                                <button class="text-blue-600 hover:text-blue-800 text-sm font-semibold transition">Xem thêm đánh giá &darr;</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showConfirmModal" @close="closeConfirmModal" maxWidth="md">
            <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Xác nhận đăng ký</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-6 p-4 bg-blue-50/50 rounded-lg border border-blue-100">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <ArrowRightEndOnRectangleIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-0.5">Bạn đang đăng ký tham gia lớp:</p>
                            <p class="font-bold text-gray-900 text-base">{{ selectedClass?.name }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                        <button @click="closeConfirmModal" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Hủy bỏ</button>
                        <button @click="confirmEnrollment" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm shadow-blue-600/30 transition-colors">Xác nhận Đăng ký</button>
                    </div>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>