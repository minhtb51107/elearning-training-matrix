<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

// Tab State
const activeTab = ref('Thông tin');

// DỮ LIỆU MOCK THEO THIẾT KẾ
const cls = ref({
    code: 'CLS-2026-SA-01',
    name: 'SALES NÂNG CAO - LỚP L1',
    time: '20/01/2026 - 22/01/2026',
    status: 'MỞ ĐĂNG KÝ', // MỞ ĐĂNG KÝ | ĐANG HỌC | KẾT THÚC
    instructor: 'Nguyễn Văn Nam',
    students_count: '30 / 35',
    shift: 'Sáng',
    scope: 'Phòng Kinh doanh',
    max_students: 35,
    note: ''
});

// Hàm đổi trạng thái mô phỏng
const changeStatus = (newStatus) => {
    cls.value.status = newStatus;
};

// Dữ liệu Tab Học viên
const students = ref([
    { id: 1, name: 'Nguyễn A', employee_code: 'NV-2023-015', department: 'Kinh doanh', email: 'a@company.com', status: 'Đã đăng kí', score: '-', result: '-', remark: '-', action: 'Gỡ' },
    { id: 2, name: 'Trần B', employee_code: 'NV-2023-016', department: 'Kinh doanh', email: 'b@company.com', status: 'Hoàn thành', score: '85', result: 'Đạt', remark: 'Tốt', action: 'Xem' },
    { id: 3, name: 'Lê C', employee_code: 'NV-2023-017', department: 'Kinh doanh', email: 'c@company.com', status: 'Chưa đạt', score: '60', result: 'Chưa đạt', remark: 'Cần cải thiện', action: 'Chấm' },
]);

// Dữ liệu Tab Tài liệu
const documents = ref([
    { id: 1, name: 'Slide_BanHang.pdf', type: 'PDF', date: '10/01' },
    { id: 2, name: 'Video kỹ năng chốt sale', type: 'LINK', date: '11/01' }
]);

// Quản lý Modal Xem Chi Tiết Học Viên
const showStudentModal = ref(false);
const selectedStudent = ref(null);

const openStudentModal = (student) => {
    selectedStudent.value = student;
    showStudentModal.value = true;
};
const closeStudentModal = () => {
    showStudentModal.value = false;
    setTimeout(() => selectedStudent.value = null, 200);
};
</script>

<template>
    <Head :title="'Chi tiết lớp học: ' + cls.code" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <div class="mb-4">
                        <Link :href="route('system.classes.index')" class="text-lg font-bold text-gray-800 hover:text-gray-500 transition">
                            &lt; Quay lại
                        </Link>
                    </div>

                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-800 uppercase mb-4">{{ cls.name }}</h2>
                        <div class="grid grid-cols-[100px_1fr] gap-y-2 text-[15px] mb-6">
                            <span class="font-bold text-gray-800">Mã lớp:</span> <span>{{ cls.code }}</span>
                            <span class="font-bold text-gray-800">Thời gian:</span> <span>{{ cls.time }}</span>
                            <span class="font-bold text-gray-800 mt-1">Trạng thái:</span> 
                            <span class="font-bold mt-1 uppercase" 
                                  :class="{
                                      'text-[#16a34a]': cls.status === 'MỞ ĐĂNG KÝ', 
                                      'text-[#d97706]': cls.status === 'ĐANG HỌC', 
                                      'text-[#dc2626]': cls.status === 'KẾT THÚC'
                                  }">
                                {{ cls.status }}
                            </span>
                        </div>

                        <div class="flex items-center gap-12 text-[15px] border-b border-gray-300 pb-8 relative">
                            <div class="absolute right-0 top-[-90px] text-right">
                                <p><span class="font-bold text-gray-800">Giảng viên:</span> {{ cls.instructor }}</p>
                                <p class="mt-2"><span class="font-bold text-gray-800">Số lượng:</span> {{ cls.students_count }}</p>
                            </div>
                            
                            <button v-if="cls.status === 'MỞ ĐĂNG KÝ'" @click="changeStatus('ĐANG HỌC')" class="text-[#16a34a] hover:text-green-700 font-bold uppercase transition">
                                [ BẮT ĐẦU HỌC ]
                            </button>
                            <button v-if="cls.status === 'ĐANG HỌC'" @click="changeStatus('KẾT THÚC')" class="text-[#c93b42] hover:text-red-800 font-bold uppercase transition">
                                [ Kết thúc ]
                            </button>
                            <button v-if="cls.status === 'KẾT THÚC'" @click="changeStatus('MỞ ĐĂNG KÝ')" class="text-[#16a34a] hover:text-green-700 font-bold uppercase transition">
                                [ Mở lại khóa học ]
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-6 mb-8 text-[15px] font-bold text-gray-500 justify-center border-b border-gray-200">
                        <button @click="activeTab = 'Thông tin'" :class="['pb-3 px-2 transition border-b-4', activeTab === 'Thông tin' ? 'text-gray-900 border-gray-800' : 'border-transparent hover:text-gray-800']">Thông tin</button>
                        <span class="text-gray-300 pb-3">|</span>
                        <button @click="activeTab = 'Học viên'" :class="['pb-3 px-2 transition border-b-4', activeTab === 'Học viên' ? 'text-gray-900 border-gray-800' : 'border-transparent hover:text-gray-800']">Học viên</button>
                        <span class="text-gray-300 pb-3">|</span>
                        <button @click="activeTab = 'Tài liệu'" :class="['pb-3 px-2 transition border-b-4', activeTab === 'Tài liệu' ? 'text-gray-900 border-gray-800' : 'border-transparent hover:text-gray-800']">Tài liệu</button>
                        <span class="text-gray-300 pb-3">|</span>
                        <button @click="activeTab = 'Kết quả'" :class="['pb-3 px-2 transition border-b-4', activeTab === 'Kết quả' ? 'text-gray-900 border-gray-800' : 'border-transparent hover:text-gray-800']">Kết quả</button>
                    </div>

                    <div v-if="activeTab === 'Thông tin'" class="bg-[#f3f4f6] p-8 rounded-sm border border-gray-300 text-[15px]">
                        <h3 class="font-bold text-gray-800 mb-6">Thông tin khóa học:</h3>
                        <div class="grid grid-cols-2 gap-x-12 gap-y-4 text-gray-800">
                            <div>
                                <p class="mb-3 font-bold">Tên lớp: <span class="font-normal ml-2 bg-white px-3 py-1 border border-gray-300 rounded shadow-sm inline-block min-w-[200px]">[ {{ cls.name }} ]</span></p>
                                <p class="mb-3 font-bold">Ca học: <span class="font-normal ml-2 bg-white px-3 py-1 border border-gray-300 rounded shadow-sm inline-block min-w-[100px]">[ {{ cls.shift }} ▼ ]</span></p>
                                <p class="mb-3 font-bold">Phạm vi đào tạo: <span class="font-normal ml-2 bg-white px-3 py-1 border border-gray-300 rounded shadow-sm inline-block min-w-[200px]">[ {{ cls.scope }} ▼ ]</span></p>
                                <p class="mb-3 font-bold">Số lượng tối đa: <span class="font-normal ml-2 bg-white px-3 py-1 border border-gray-300 rounded shadow-sm inline-block min-w-[100px]">[ {{ cls.max_students }} ]</span></p>
                                
                                <div class="mt-4">
                                    <p class="font-bold mb-1">Ghi chú:</p>
                                    <textarea rows="4" class="w-full bg-white border-gray-300 rounded shadow-sm text-sm resize-none"></textarea>
                                </div>
                            </div>
                            <div>
                                <p class="mb-4 font-bold">Giảng viên: <span class="font-normal ml-2">{{ cls.instructor }}</span></p>
                                <p class="font-bold mb-2">Thời gian học:</p>
                                <p class="mb-2">- Từ ngày: <span class="font-normal ml-2 bg-white px-3 py-1 border border-gray-300 rounded shadow-sm inline-block min-w-[150px]">[ 20/01/2026 ]</span></p>
                                <p class="mb-2">- Đến ngày: <span class="font-normal ml-2 bg-white px-3 py-1 border border-gray-300 rounded shadow-sm inline-block min-w-[150px]">[ 22/01/2026 ]</span></p>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeTab === 'Học viên'">
                        <h3 class="text-base font-bold text-gray-800 mb-4">Danh sách học viên</h3>
                        <div class="flex gap-6 text-[#16a34a] font-bold text-[15px] mb-4">
                            <button class="hover:underline transition">[ + THÊM HỌC VIÊN ]</button>
                            <button class="hover:underline transition">[ XUẤT FILE EXCEL ]</button>
                        </div>
                        <div class="overflow-x-auto border border-gray-300">
                            <table class="min-w-full divide-y divide-gray-300 text-center text-[14px]">
                                <thead class="bg-[#dcfce7]">
                                    <tr>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/6">Nhân viên</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/6">Phòng ban</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/4">Email</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/6">Trạng thái</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/12">Điểm</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 w-1/6">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-300">
                                    <tr v-for="stu in students" :key="stu.id" class="hover:bg-gray-50 transition cursor-pointer" @click="openStudentModal(stu)">
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ stu.name }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800">{{ stu.department }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ stu.email }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800">{{ stu.status }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800 font-bold">{{ stu.score }}</td>
                                        <td class="px-4 py-3">
                                            <button @click.stop="stu.action === 'Xem' ? openStudentModal(stu) : null" class="text-[#0ea5e9] hover:underline font-medium">
                                                [ {{ stu.action }} ]
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="activeTab === 'Tài liệu'">
                        <h3 class="text-base font-bold text-gray-800 mb-4 border-b border-gray-300 pb-2">Tài liệu lớp học</h3>
                        <div class="mb-4">
                            <button class="text-[#d97706] font-bold text-[15px] hover:underline transition">[ + TẢI LÊN TÀI LIỆU ]</button>
                        </div>
                        <div class="overflow-x-auto border border-gray-300 w-3/4">
                            <table class="min-w-full divide-y divide-gray-300 text-center text-[14px]">
                                <thead class="bg-[#fcd38e]">
                                    <tr>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-2/5">Tên tài liệu</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/5">Loại</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300 w-1/5">Ngày</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 w-1/5">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-300">
                                    <tr v-for="doc in documents" :key="doc.id" class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800">{{ doc.name }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ doc.type }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ doc.date }}</td>
                                        <td class="px-4 py-3">
                                            <button class="text-[#0ea5e9] hover:underline font-medium">[ Xóa ]</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="activeTab === 'Kết quả'">
                        <h3 class="text-base font-bold text-gray-800 mb-6 border-b border-gray-300 pb-2">Kết quả - Thống kê</h3>
                        
                        <div class="grid grid-cols-3 gap-8 w-3/4 mx-auto mb-8">
                            <div class="border border-gray-300 rounded shadow-sm p-4 text-center">
                                <p class="font-bold text-gray-800 mb-1">Tổng học viên:</p>
                                <p class="text-2xl font-bold text-[#0ea5e9]">30</p>
                            </div>
                            <div class="border border-gray-300 rounded shadow-sm p-4 text-center">
                                <p class="font-bold text-gray-800 mb-1">Hoàn thành:</p>
                                <p class="text-2xl font-bold text-[#16a34a]">22</p>
                            </div>
                            <div class="border border-gray-300 rounded shadow-sm p-4 text-center">
                                <p class="font-bold text-gray-800 mb-1">Tỷ lệ:</p>
                                <p class="text-2xl font-bold text-[#d97706]">73%</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto border border-gray-300">
                            <table class="min-w-full divide-y divide-gray-300 text-center text-[14px]">
                                <thead class="bg-[#bae6fd]">
                                    <tr>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-blue-200">Nhân viên</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-blue-200">Phòng ban</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-blue-200">Điểm</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-blue-200">Kết quả</th>
                                        <th class="px-4 py-3 font-bold text-gray-900">Nhận xét</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-300">
                                    <tr v-for="stu in students.filter(s => s.score !== '-')" :key="stu.id" class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800">{{ stu.name }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ stu.department }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 font-bold">{{ stu.score }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300" :class="stu.result === 'Đạt' ? 'text-gray-800' : 'text-red-600'">{{ stu.result }}</td>
                                        <td class="px-4 py-3 text-gray-600 italic">{{ stu.remark }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="activeTab !== 'Thông tin'" class="mt-6 flex justify-center items-center gap-4 text-sm text-[#0ea5e9] font-medium">
                        <button class="hover:underline">&lt; Prev</button>
                        <button class="w-7 h-7 rounded bg-blue-100 font-bold flex items-center justify-center">1</button>
                        <button class="w-7 h-7 rounded hover:bg-gray-100 flex items-center justify-center text-gray-600">2</button>
                        <button class="w-7 h-7 rounded hover:bg-gray-100 flex items-center justify-center text-gray-600">3</button>
                        <button class="hover:underline">Next &gt;</button>
                    </div>

                </div>
            </div>
        </div>

        <Modal :show="showStudentModal" @close="closeStudentModal" maxWidth="md">
            <div class="p-8 bg-white border-2 border-gray-300 shadow-xl" v-if="selectedStudent">
                <h3 class="text-lg font-bold text-gray-800 uppercase mb-4 border-b border-gray-300 pb-2">Chi tiết học viên</h3>
                
                <div class="mb-6 text-[15px] text-gray-800 leading-relaxed">
                    <p class="font-bold text-base mb-1">{{ selectedStudent.name }}</p>
                    <p><span class="font-bold">Mã NV:</span> {{ selectedStudent.employee_code }}</p>
                    <p><span class="font-bold">Phòng ban:</span> [ {{ selectedStudent.department }} ]</p>
                    <p><span class="font-bold">Email:</span> {{ selectedStudent.email }}</p>
                </div>

                <div class="mb-8 text-[15px] text-gray-800 leading-relaxed">
                    <p class="font-bold uppercase mb-2">Lớp học hiện tại:</p>
                    <p><span class="font-bold">Tên lớp:</span> {{ cls.name }}</p>
                    <p><span class="font-bold">Trạng thái:</span> [ {{ selectedStudent.status }} ]</p>
                    <p v-if="selectedStudent.score !== '-'"><span class="font-bold">Điểm:</span> <span class="font-bold text-[#16a34a]">{{ selectedStudent.score }} - {{ selectedStudent.result }}</span></p>
                    <p v-if="selectedStudent.score !== '-'"><span class="font-bold">Ngày hoàn thành:</span> 22/01/2026</p>
                </div>

                <div class="flex justify-center border-t border-gray-200 pt-4 mt-4">
                    <button @click="closeStudentModal" class="text-[#b91c1c] font-bold text-base uppercase hover:underline transition">
                        [ ĐÓNG ]
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>