<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

// 1. Nhận dữ liệu THẬT từ Controller truyền sang
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

// 2. Hàm gọi API để lưu đăng ký vào Database
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
        <div class="py-6">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    {{ $page.props.flash.success }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <div class="mb-6">
                        <Link :href="route('employee.courses.index')" class="text-lg font-bold text-gray-800 hover:text-gray-500 transition">
                            &lt; Quay lại
                        </Link>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-800 uppercase mb-8">{{ course.name }}</h2>

                    <div class="flex items-center gap-6 text-[15px] font-bold text-gray-500 border-b border-gray-300 pb-2 mb-8 w-1/2">
                        <button @click="activeTab = 'Giới thiệu'" :class="['px-2 transition', activeTab === 'Giới thiệu' ? 'text-gray-900 border-b-2 border-gray-800 pb-1' : 'hover:text-gray-800']">Giới thiệu</button>
                        <span class="text-gray-300 font-bold">|</span>
                        <button @click="activeTab = 'Nội dung'" :class="['px-2 transition', activeTab === 'Nội dung' ? 'text-gray-900 border-b-2 border-gray-800 pb-1' : 'hover:text-gray-800']">Nội dung</button>
                        <span class="text-gray-300 font-bold">|</span>
                        <button @click="activeTab = 'Đánh giá'" :class="['px-2 transition', activeTab === 'Đánh giá' ? 'text-gray-900 border-b-2 border-gray-800 pb-1' : 'hover:text-gray-800']">Đánh giá</button>
                    </div>

                    <div v-if="activeTab === 'Giới thiệu'">
                        
                        <div class="bg-[#f3f4f6] p-6 rounded-sm mb-10">
                            <h3 class="font-bold text-gray-800 mb-4 uppercase">GIỚI THIỆU</h3>
                            <div class="text-[14px] text-gray-800 font-bold leading-relaxed space-y-1">
                                <p v-for="(item, idx) in course.overview" :key="idx">{{ item }}</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-bold text-gray-800 mb-4">Danh sách lớp học:</h3>
                            <div class="space-y-4">
                                <div v-if="!course.classes || course.classes.length === 0" class="text-gray-500 italic p-4 border border-gray-200 bg-gray-50">
                                    Khóa học này hiện chưa có lớp nào đang mở.
                                </div>

                                <div v-for="cls in course.classes" :key="cls.id" class="border border-gray-300 p-5 rounded-sm relative shadow-sm">
                                    <h4 class="text-base font-bold text-gray-800 mb-1">LỚP: {{ cls.name }}</h4>
                                    <p class="text-[13px] text-gray-800 font-bold mb-3">Ngày dự kiến: {{ cls.time }}</p>
                                    <p class="text-[14px] text-gray-800 font-bold mb-1">Giảng viên: {{ cls.instructor }}</p>
                                    
                                    <p v-if="!cls.isFull" class="text-[14px] text-gray-800 font-bold">Còn trống: {{ cls.slot }}</p>
                                    <p v-else class="text-[14px] font-bold text-red-600">{{ cls.slot }}</p>

                                    <button v-if="cls.isEnrolled" disabled class="absolute right-5 bottom-5 bg-gray-200 text-gray-500 border border-gray-300 font-bold py-1.5 px-6 rounded-sm text-[13px] cursor-not-allowed">
                                        Đã đăng ký
                                    </button>
                                    <button v-else-if="!cls.isFull" @click="openConfirmModal(cls)" class="absolute right-5 bottom-5 bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-1.5 px-6 rounded-sm shadow-sm transition text-[13px]">
                                        Đăng ký lớp
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div v-if="activeTab === 'Nội dung'" class="w-2/3">
                        <h3 class="font-bold text-gray-800 mb-4 uppercase border-b border-gray-300 pb-2">NỘI DUNG</h3>
                        <ul class="divide-y divide-gray-200">
                            <li v-for="(item, idx) in course.content" :key="idx" class="py-4 flex justify-between items-center text-[14px] text-gray-800 font-bold">
                                <span>{{ item.title }}</span>
                                <span>{{ item.time }}</span>
                            </li>
                        </ul>
                    </div>

                    <div v-if="activeTab === 'Đánh giá'" class="w-2/3">
                        <h3 class="font-bold text-gray-800 mb-4 uppercase border-b border-gray-300 pb-2">REVIEWS</h3>
                        <p class="font-bold text-[15px] text-gray-800 mb-2">Đánh giá trung bình: <span class="tracking-widest">★★★★☆</span> 4.2/5</p>
                        <button class="text-[#0ea5e9] hover:underline text-sm font-bold mb-8 transition">[ Viết đánh giá ]</button>

                        <h4 class="font-bold text-gray-800 text-[15px] mb-4">Tất cả đánh giá:</h4>
                        <div class="space-y-6">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">12/01/2026</p>
                                <p class="font-bold text-[14px] text-gray-800">Nguyễn A: <span class="tracking-widest font-normal">★★★★★</span></p>
                                <p class="text-sm text-gray-700 italic">" Nội dung dễ hiểu....... "</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">12/01/2026</p>
                                <p class="font-bold text-[14px] text-gray-800">Nguyễn B: <span class="tracking-widest font-normal">★★★★☆</span></p>
                                <p class="text-sm text-gray-700 italic">" Quiz hơi khó.... "</p>
                            </div>
                        </div>

                        <div class="mt-6 text-center">
                            <button class="text-[#0ea5e9] hover:underline text-[13px] font-bold uppercase transition">[ Xem thêm ]</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <Modal :show="showConfirmModal" @close="closeConfirmModal" maxWidth="md">
            <div class="p-6 bg-white border border-gray-300 text-center">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Xác nhận đăng ký</h3>
                <p class="text-[15px] text-gray-700 mb-6">Bạn có chắc chắn muốn đăng ký tham gia <strong>{{ selectedClass?.name }}</strong> không?</p>
                <div class="flex justify-center gap-4">
                    <button @click="closeConfirmModal" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded transition">Hủy</button>
                    <button @click="confirmEnrollment" class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-2 px-6 rounded transition">Xác nhận</button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>