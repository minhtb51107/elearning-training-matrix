<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    courseClass: Object,
    availableUsers: Array
});

const activeTab = ref('Thông tin');

const formatTimeRange = (start, end) => {
    if (!start || !end) return '--';
    const startDate = new Date(start).toLocaleDateString('vi-VN');
    const endDate = new Date(end).toLocaleDateString('vi-VN');
    return `${startDate} - ${endDate}`;
};

const cls = computed(() => ({
    id: props.courseClass.id,
    code: props.courseClass.code,
    name: props.courseClass.name,
    time: formatTimeRange(props.courseClass.start_date, props.courseClass.end_date),
    status: props.courseClass.status.toUpperCase(),
    instructor: props.courseClass.instructor?.name || 'Chưa phân công',
    students_count: `${props.courseClass.enrollments?.length || 0} / ${props.courseClass.max_students}`,
    shift: 'Sáng',
    scope: props.courseClass.department?.name || 'Toàn công ty',
    max_students: props.courseClass.max_students,
    note: ''
}));

const changeStatus = (newStatus) => {
    if (confirm(`Bạn có chắc chắn muốn chuyển trạng thái lớp học thành: ${newStatus}?`)) {
        router.put(route('system.classes.update-status', cls.value.id), { status: newStatus }, { preserveScroll: true });
    }
};

const students = computed(() => {
    const statusMap = {
        'enrolled': 'Đã đăng ký',
        'in_progress': 'Đang học',
        'completed': 'Hoàn thành',
        'failed': 'Chưa đạt'
    };

    return (props.courseClass.enrollments || []).map(enrol => ({
        id: enrol.user.id,
        name: enrol.user.name,
        employee_code: `NV-${String(enrol.user.id).padStart(3, '0')}`,
        department: enrol.user.department?.name || '--',
        email: enrol.user.email,
        status: statusMap[enrol.status] || enrol.status, 
        score: enrol.final_score || '-',
        result: enrol.status === 'completed' ? 'Đạt' : (enrol.status === 'failed' ? 'Chưa đạt' : '-'),
        remark: '-'
    }));
});

const showAddStudentModal = ref(false);
const selectedUserIds = ref([]);

const submitAddStudents = () => {
    if(selectedUserIds.value.length === 0) return alert('Vui lòng chọn ít nhất 1 học viên');
    router.post(route('system.classes.add-students', props.courseClass.id), {
        user_ids: selectedUserIds.value
    }, {
        onSuccess: () => {
            showAddStudentModal.value = false;
            selectedUserIds.value = [];
        },
        preserveScroll: true
    });
};

const removeStudent = (studentId, studentName) => {
    if (confirm(`Bạn có chắc chắn muốn gỡ học viên [ ${studentName} ] khỏi lớp này?`)) {
        router.delete(route('system.classes.remove-student', { 
            courseClass: props.courseClass.id, 
            studentId: studentId 
        }), { preserveScroll: true });
    }
};

// ==========================================
// LOGIC QUẢN LÝ TÀI LIỆU
// ==========================================
const documents = computed(() => {
    return (props.courseClass.documents || []).map(doc => ({
        id: doc.id,
        name: doc.name,
        type: doc.type.toUpperCase(),
        date: new Date(doc.created_at).toLocaleDateString('vi-VN'),
        url: doc.url
    }));
});

const showAddDocModal = ref(false);
const docForm = useForm({
    name: '',
    type: 'pdf',
    file: null,
    url: ''
});

const submitAddDoc = () => {
    // Nếu upload file vật lý
    if (docForm.type !== 'link' && !docForm.file) {
        alert('Vui lòng chọn một file để tải lên!');
        return;
    }
    // Nếu up link
    if (docForm.type === 'link' && !docForm.url) {
        alert('Vui lòng nhập đường dẫn URL!');
        return;
    }

    docForm.post(route('system.classes.upload-document', props.courseClass.id), {
        onSuccess: () => {
            showAddDocModal.value = false;
            docForm.reset();
        },
        preserveScroll: true
    });
};

const deleteDoc = (doc) => {
    if (confirm(`Bạn có chắc chắn muốn xóa tài liệu [ ${doc.name} ]?`)) {
        router.delete(route('system.classes.delete-document', doc.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="'Chi tiết lớp học: ' + cls.code" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    {{ $page.props.flash.success }}
                </div>

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
                                      'text-[#dc2626]': cls.status === 'KẾT THÚC',
                                      'text-gray-500': cls.status === 'NHÁP'
                                  }">
                                {{ cls.status }}
                            </span>
                        </div>

                        <div class="flex items-center gap-12 text-[15px] border-b border-gray-300 pb-8 relative">
                            <div class="absolute right-0 top-[-90px] text-right">
                                <p><span class="font-bold text-gray-800">Giảng viên:</span> {{ cls.instructor }}</p>
                                <p class="mt-2"><span class="font-bold text-gray-800">Số lượng:</span> <span class="text-blue-600 font-bold">{{ cls.students_count }}</span></p>
                            </div>
                            
                            <button v-if="cls.status === 'MỞ ĐĂNG KÝ'" @click="changeStatus('Đang học')" class="text-[#16a34a] hover:text-green-700 font-bold uppercase transition">
                                [ BẮT ĐẦU HỌC ]
                            </button>
                            <button v-if="cls.status === 'ĐANG HỌC'" @click="changeStatus('Kết thúc')" class="text-[#c93b42] hover:text-red-800 font-bold uppercase transition">
                                [ Kết thúc ]
                            </button>
                            <button v-if="cls.status === 'KẾT THÚC'" @click="changeStatus('Mở đăng ký')" class="text-[#16a34a] hover:text-green-700 font-bold uppercase transition">
                                [ Mở lại khóa học ]
                            </button>
                            <button v-if="cls.status === 'NHÁP'" @click="changeStatus('Mở đăng ký')" class="text-blue-600 hover:text-blue-800 font-bold uppercase transition">
                                [ XUẤT BẢN / MỞ ĐĂNG KÝ ]
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
                                <p class="mb-3 font-bold">Phạm vi: <span class="font-normal ml-2 bg-white px-3 py-1 border border-gray-300 rounded shadow-sm inline-block min-w-[200px]">[ {{ cls.scope }} ]</span></p>
                                <p class="mb-3 font-bold">SL tối đa: <span class="font-normal ml-2 bg-white px-3 py-1 border border-gray-300 rounded shadow-sm inline-block min-w-[100px]">[ {{ cls.max_students }} ]</span></p>
                            </div>
                            <div>
                                <p class="mb-4 font-bold">Giảng viên: <span class="font-normal ml-2">{{ cls.instructor }}</span></p>
                                <p class="font-bold mb-2">Thời gian học:</p>
                                <p class="mb-2">- Từ ngày: <span class="font-normal ml-2 bg-white px-3 py-1 border border-gray-300 rounded shadow-sm inline-block min-w-[150px]">[ {{ new Date(courseClass.start_date).toLocaleDateString('vi-VN') }} ]</span></p>
                                <p class="mb-2">- Đến ngày: <span class="font-normal ml-2 bg-white px-3 py-1 border border-gray-300 rounded shadow-sm inline-block min-w-[150px]">[ {{ new Date(courseClass.end_date).toLocaleDateString('vi-VN') }} ]</span></p>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeTab === 'Học viên'">
                        <h3 class="text-base font-bold text-gray-800 mb-4">Danh sách học viên ({{ students.length }})</h3>
                        <div class="flex gap-6 text-[#16a34a] font-bold text-[15px] mb-4">
                            <button @click="showAddStudentModal = true" class="hover:underline transition">[ + THÊM HỌC VIÊN ]</button>
                            <button class="hover:underline transition text-gray-500">[ XUẤT FILE EXCEL ]</button>
                        </div>
                        <div class="overflow-x-auto border border-gray-300">
                            <table class="min-w-full divide-y divide-gray-300 text-center text-[14px]">
                                <thead class="bg-[#dcfce7]">
                                    <tr>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Nhân viên</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Phòng ban</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Email</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Trạng thái</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Điểm</th>
                                        <th class="px-4 py-3 font-bold text-gray-900">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-300">
                                    <tr v-if="students.length === 0">
                                        <td colspan="6" class="px-4 py-6 text-gray-500 italic">Lớp học này chưa có học viên nào.</td>
                                    </tr>
                                    <tr v-for="stu in students" :key="stu.id" class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700 font-bold text-left">{{ stu.name }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800">{{ stu.department }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700 text-left">{{ stu.email }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800">{{ stu.status }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-800 font-bold">{{ stu.score }}</td>
                                        <td class="px-4 py-3">
                                            <button @click="removeStudent(stu.id, stu.name)" class="text-red-600 hover:text-red-800 font-medium hover:underline">
                                                [ Gỡ ]
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="activeTab === 'Tài liệu'">
                        <h3 class="text-base font-bold text-gray-800 mb-4 border-b border-gray-300 pb-2">Tài liệu lớp học ({{ documents.length }})</h3>
                        <div class="mb-4">
                            <button @click="showAddDocModal = true" class="text-[#d97706] font-bold text-[15px] hover:underline transition">
                                [ + TẢI LÊN TÀI LIỆU ]
                            </button>
                        </div>
                        <div class="overflow-x-auto border border-gray-300 w-full md:w-3/4">
                            <table class="min-w-full divide-y divide-gray-300 text-center text-[14px]">
                                <thead class="bg-[#fcd38e]">
                                    <tr>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Tên tài liệu</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Loại</th>
                                        <th class="px-4 py-3 font-bold text-gray-900 border-r border-gray-300">Ngày tải lên</th>
                                        <th class="px-4 py-3 font-bold text-gray-900">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-300">
                                    <tr v-if="documents.length === 0">
                                        <td colspan="4" class="px-4 py-6 text-gray-500 italic">Lớp học này chưa có tài liệu nào.</td>
                                    </tr>
                                    <tr v-for="doc in documents" :key="doc.id" class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 border-r border-gray-300 text-blue-600 font-bold text-left">
                                           <a :href="doc.url" 
   target="_blank" 
   :download="doc.type !== 'LINK' ? doc.name : false" 
   class="text-[#0ea5e9] hover:text-blue-800 hover:underline font-bold transition">
    {{ doc.name }}
</a>
                                        </td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ doc.type }}</td>
                                        <td class="px-4 py-3 border-r border-gray-300 text-gray-700">{{ doc.date }}</td>
                                        <td class="px-4 py-3">
                                            <button @click="deleteDoc(doc)" class="text-[#c93b42] hover:underline font-medium">[ Xóa ]</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="activeTab === 'Kết quả'">
                        <h3 class="text-base font-bold text-gray-800 mb-6 border-b border-gray-300 pb-2">Kết quả - Thống kê</h3>
                        <p class="text-gray-500 italic text-center">Chức năng thống kê điểm đang được phát triển...</p>
                    </div>

                </div>
            </div>
        </div>

        <Modal :show="showAddStudentModal" @close="showAddStudentModal = false" maxWidth="2xl">
            <div class="p-6 bg-white">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Thêm học viên vào lớp</h3>
                <div class="max-h-[400px] overflow-y-auto mb-4 border border-gray-300 rounded">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 w-10 text-center"><input type="checkbox" disabled></th>
                                <th class="px-4 py-2 font-bold text-gray-800">Tên nhân viên</th>
                                <th class="px-4 py-2 font-bold text-gray-800">Phòng ban</th>
                                <th class="px-4 py-2 font-bold text-gray-800">Email</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-if="availableUsers.length === 0">
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">Không có nhân viên nào phù hợp hoặc tất cả đã được thêm.</td>
                            </tr>
                            <tr v-for="user in availableUsers" :key="user.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" :value="user.id" v-model="selectedUserIds" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-4 py-2 text-gray-800 font-medium">{{ user.name }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ user.department?.name || '--' }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ user.email }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <button @click="showAddStudentModal = false" class="px-4 py-2 text-gray-600 font-bold border border-gray-300 rounded hover:bg-gray-100">Hủy</button>
                    <button @click="submitAddStudents" class="px-4 py-2 bg-[#16a34a] text-white font-bold rounded hover:bg-green-700 shadow">Xác nhận thêm ({{ selectedUserIds.length }})</button>
                </div>
            </div>
        </Modal>

<Modal :show="showAddDocModal" @close="showAddDocModal = false" maxWidth="md">
            <form @submit.prevent="submitAddDoc" class="p-6 bg-white">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-200 pb-2">Thêm tài liệu mới</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tên tài liệu: <span class="text-red-500">*</span></label>
                    <input v-model="docForm.name" type="text" class="w-full border-gray-300 rounded focus:ring-blue-500 text-sm">
                    <div v-if="docForm.errors.name" class="text-red-600 text-[13px] font-medium mt-1">{{ docForm.errors.name }}</div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Loại tài liệu:</label>
                    <select v-model="docForm.type" class="w-full border-gray-300 rounded focus:ring-blue-500 text-sm">
                        <option value="pdf">File PDF</option>
                        <option value="doc">File Word</option>
                        <option value="pptx">File PowerPoint</option>
                        <option value="video">File Video</option>
                        <option value="link">Link đính kèm</option>
                    </select>
                    <div v-if="docForm.errors.type" class="text-red-600 text-[13px] font-medium mt-1">{{ docForm.errors.type }}</div>
                </div>

                <div v-if="docForm.type === 'link'" class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Đường dẫn (URL): <span class="text-red-500">*</span></label>
                    <input v-model="docForm.url" type="url" placeholder="https://..." class="w-full border-gray-300 rounded focus:ring-blue-500 text-sm">
                    <div v-if="docForm.errors.url" class="text-red-600 text-[13px] font-medium mt-1">{{ docForm.errors.url }}</div>
                </div>

                <div v-else class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Chọn File: <span class="text-red-500">*</span></label>
                    <input type="file" @input="docForm.file = $event.target.files[0]" class="w-full border border-gray-300 p-1.5 rounded text-sm bg-gray-50">
                    <p class="text-xs text-gray-500 mt-1">Dung lượng tối đa: 20MB.</p>
                    <div v-if="docForm.errors.file" class="text-red-600 text-[13px] font-medium mt-1">{{ docForm.errors.file }}</div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" @click="showAddDocModal = false" class="px-4 py-2 text-gray-600 font-bold border border-gray-300 rounded hover:bg-gray-100 transition">Hủy</button>
                    <button type="submit" :disabled="docForm.processing" class="px-4 py-2 bg-[#d97706] text-white font-bold rounded hover:bg-orange-600 shadow disabled:opacity-50 transition">
                        <span v-if="docForm.processing">Đang tải lên...</span>
                        <span v-else>Tải lên</span>
                    </button>
                </div>
            </form>
        </Modal>

    </AuthenticatedLayout>
</template>