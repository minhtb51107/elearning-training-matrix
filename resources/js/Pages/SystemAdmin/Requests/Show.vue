<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

// MOCK DATA: Chép chính xác từ Mockup
const req = ref({
    code: 'YC-2026-000123',
    department: 'Kinh doanh',
    requester: 'Nguyen Nguyen',
    date: '12/01/2026',
    status: 'ĐANG CHỜ DUYỆT',
    course_name: 'Kỹ năng bán hàng',
    target_audience: 'Toàn phòng',
    content: 'Khóa học nhằm trang bị cho nhân viên kinh doanh các kỹ năng bán hàng nâng cao, bao gồm tiếp cận khách hàng, xử lý từ chối và chốt sale hiệu quả.',
    expected_duration: '8 giờ',
    notes: '',
    hr_feedback: ''
});

// State Quản lý Modals
const showApproveModal = ref(false);
const showRejectModal = ref(false);

const approveForm = ref({
    created_course_name: 'Kỹ năng bán hàng chuyên sâu', // Gợi ý tên dựa trên yêu cầu
    note: ''
});

const rejectForm = ref({
    reason: ''
});

// Hàm bật tắt
const openApproveModal = () => showApproveModal.value = true;
const closeApproveModal = () => showApproveModal.value = false;

const openRejectModal = () => showRejectModal.value = true;
const closeRejectModal = () => showRejectModal.value = false;
</script>

<template>
    <Head :title="'Chi tiết Yêu cầu: ' + req.code" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Chi tiết yêu cầu đào tạo</h2>

                    <div class="mb-8">
                        <h3 class="font-bold text-[15px] text-gray-800 mb-3">Thông tin chung:</h3>
                        <div class="bg-[#e5e7eb] p-6 rounded-sm text-[15px]">
                            <div class="grid grid-cols-[120px_1fr] gap-y-3 text-gray-800">
                                <span class="font-bold">Mã yêu cầu:</span> <span>{{ req.code }}</span>
                                <span class="font-bold">Phòng ban:</span> <span>{{ req.department }}</span>
                                <span class="font-bold">Người gửi:</span> <span>{{ req.requester }}</span>
                                <span class="font-bold">Ngày gửi:</span> <span>{{ req.date }}</span>
                                <span class="font-bold">Trạng thái:</span> <span class="uppercase tracking-wide">{{ req.status }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="font-bold text-[15px] text-gray-800 mb-3">Thông tin yêu cầu đào tạo:</h3>
                        
                        <div class="grid grid-cols-2 gap-6 mb-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tên khóa học:</label>
                                <input :value="req.course_name" type="text" disabled class="w-full bg-gray-50 border-gray-300 rounded text-gray-600">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Đối tượng / Phạm vi đào tạo:</label>
                                <select disabled class="w-full bg-gray-50 border-gray-300 rounded text-gray-600 appearance-none">
                                    <option>{{ req.target_audience }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6 mb-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nội dung đào tạo:</label>
                                <textarea :value="req.content" rows="4" disabled class="w-full bg-gray-50 border-gray-300 rounded text-gray-600 resize-none italic"></textarea>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Thời lượng dự kiến:</label>
                                    <input :value="req.expected_duration" type="text" disabled class="w-full bg-gray-50 border-gray-300 rounded text-gray-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Ghi chú thêm (nếu có):</label>
                                    <input :value="req.notes" type="text" disabled class="w-full bg-gray-50 border-gray-300 rounded text-gray-400 placeholder-gray-400" placeholder="(nếu có)">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Phản hồi từ phòng Đào Tạo: (nếu có)</label>
                            <input :value="req.hr_feedback" type="text" disabled class="w-full bg-gray-50 border-gray-300 rounded text-gray-400 placeholder-gray-400" placeholder="(Chỉ hiển thị khi bị từ chối hoặc bổ sung)">
                        </div>
                    </div>

                    <div v-if="req.status === 'ĐANG CHỜ DUYỆT'" class="flex justify-center gap-6 mt-8">
                        <button @click="openRejectModal" class="bg-[#c93b42] hover:bg-red-800 text-white font-bold py-2.5 px-12 rounded shadow transition">
                            Từ chối
                        </button>
                        <button @click="openApproveModal" class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-2.5 px-12 rounded shadow transition">
                            Duyệt
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <Modal :show="showApproveModal" @close="closeApproveModal" maxWidth="md">
            <div class="p-6 bg-white border-2 border-gray-300">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Duyệt yêu cầu đào tạo</h2>
                
                <div class="bg-gray-200 p-4 mb-4 text-sm border border-gray-300">
                    <div class="grid grid-cols-[120px_1fr] gap-y-1">
                        <span class="font-bold text-gray-700">Mã yêu cầu:</span> <span>{{ req.code }}</span>
                        <span class="font-bold text-gray-700">Phòng ban:</span> <span>{{ req.department }}</span>
                        <span class="font-bold text-gray-700">Tên yêu cầu:</span> <span>{{ req.course_name }}</span>
                    </div>
                </div>

                <div class="space-y-4 mb-6">
                    <div class="grid grid-cols-[150px_1fr] items-center">
                        <label class="text-sm font-bold text-gray-800">Tên khóa học sẽ tạo:</label>
                        <input v-model="approveForm.created_course_name" type="text" class="w-full border-gray-400 rounded-sm shadow-sm py-1.5 text-sm">
                    </div>
                    <div class="grid grid-cols-[150px_1fr] items-center">
                        <label class="text-sm font-bold text-gray-800">Ghi chú (optional):</label>
                        <input v-model="approveForm.note" type="text" class="w-full border-gray-400 rounded-sm shadow-sm py-1.5 text-sm placeholder-gray-400" placeholder="Xin mời nhập vào đây...">
                    </div>
                </div>

                <p class="text-center text-[15px] text-gray-800 italic mb-6">Bạn có chắc chắn muốn <span class="font-bold">DUYỆT</span> yêu cầu đào tạo này không?</p>

                <div class="flex justify-center gap-6">
                    <button @click="closeApproveModal" class="bg-[#c93b42] hover:bg-red-800 text-white font-bold py-2 px-10 rounded shadow transition">Hủy</button>
                    <button class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-2 px-10 rounded shadow transition">Duyệt</button>
                </div>
            </div>
        </Modal>

        <Modal :show="showRejectModal" @close="closeRejectModal" maxWidth="md">
            <div class="p-6 bg-white border-2 border-gray-300">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Từ chối yêu cầu đào tạo</h2>
                
                <div class="bg-gray-200 p-4 mb-4 text-sm border border-gray-300">
                    <div class="grid grid-cols-[120px_1fr] gap-y-1">
                        <span class="font-bold text-gray-700">Mã yêu cầu:</span> <span>{{ req.code }}</span>
                        <span class="font-bold text-gray-700">Phòng ban:</span> <span>{{ req.department }}</span>
                        <span class="font-bold text-gray-700">Tên yêu cầu:</span> <span>{{ req.course_name }}</span>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-800 mb-1">Lý do từ chối: <span class="text-red-500">*</span></label>
                    <textarea v-model="rejectForm.reason" rows="3" class="w-full border-gray-400 rounded-sm shadow-sm text-sm placeholder-gray-400" placeholder="VD: Trùng nội dung đào tạo hiện có / Không ưu tiên Q1..."></textarea>
                </div>

                <p class="text-center text-[15px] text-gray-800 italic mb-6">Bạn có chắc chắn muốn <span class="font-bold">TỪ CHỐI</span> yêu cầu đào tạo này không?</p>

                <div class="flex justify-center gap-6">
                    <button @click="closeRejectModal" class="bg-[#b7eb8f] hover:bg-[#95de64] text-gray-900 border border-[#95de64] font-bold py-2 px-10 rounded shadow transition">Hủy</button>
                    <button class="bg-[#c93b42] hover:bg-red-800 text-white font-bold py-2 px-10 rounded shadow transition">Từ chối</button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>