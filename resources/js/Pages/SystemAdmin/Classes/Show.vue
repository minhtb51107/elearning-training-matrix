<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import axios from 'axios'; // Bổ sung axios để gọi API
import { 
    ChevronLeftIcon, 
    UsersIcon, 
    DocumentTextIcon, 
    LinkIcon, 
    XMarkIcon, 
    UserPlusIcon, 
    ArrowUpTrayIcon,
    MagnifyingGlassIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/20/solid';

const props = defineProps({
    courseClass: Object,
    availableUsers: Array, // Mảng cũ (giờ có thể không dùng nhiều vì đã dùng API)
    resultsData: Array 
});

const activeTab = ref('Học viên'); 

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
    shift: props.courseClass.shift || 'Chưa xác định', 
    scope: props.courseClass.department?.name || 'Toàn công ty',
    max_students: props.courseClass.max_students,
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
    }));
});

const removeStudent = (studentId, studentName) => {
    if (confirm(`Bạn có chắc chắn muốn gỡ học viên [ ${studentName} ] khỏi lớp này?`)) {
        router.delete(route('system.classes.remove-student', { 
            courseClass: props.courseClass.id, 
            studentId: studentId 
        }), { preserveScroll: true });
    }
};

// ==========================================
// LOGIC MODAL CHỈ ĐỊNH ĐÀO TẠO THÔNG MINH
// ==========================================
const showingAssignModal = ref(false);
const eligibleEmployees = ref([]);
const isLoadingEmployees = ref(false);
const searchQuery = ref('');

const assignForm = useForm({
    employee_ids: [],
    deadline: ''
});

// Gọi API lấy danh sách học viên thỏa mãn điều kiện
const loadEligibleEmployees = async () => {
    isLoadingEmployees.value = true;
    try {
        const response = await axios.get(route('system.classes.eligible-employees', props.courseClass.id));
        eligibleEmployees.value = response.data;
    } catch (error) {
        console.error("Lỗi tải danh sách:", error);
    } finally {
        isLoadingEmployees.value = false;
    }
};

const openAssignModal = () => {
    showingAssignModal.value = true;
    assignForm.reset();
    searchQuery.value = '';
    loadEligibleEmployees();
};

const closeAssignModal = () => {
    showingAssignModal.value = false;
    assignForm.reset();
};

const filteredUsers = computed(() => {
    if (!searchQuery.value) return eligibleEmployees.value;
    const lowerQuery = searchQuery.value.toLowerCase();
    
    return eligibleEmployees.value.filter(user => 
        user.name.toLowerCase().includes(lowerQuery) ||
        user.email.toLowerCase().includes(lowerQuery) ||
        (user.department?.name || '').toLowerCase().includes(lowerQuery) ||
        (user.job_title || '').toLowerCase().includes(lowerQuery)
    );
});

const isAllSelected = computed(() => {
    if (filteredUsers.value.length === 0) return false;
    return filteredUsers.value.every(user => assignForm.employee_ids.includes(user.id));
});

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        const newIds = filteredUsers.value.map(u => u.id);
        assignForm.employee_ids = [...new Set([...assignForm.employee_ids, ...newIds])];
    } else {
        const filteredIds = filteredUsers.value.map(u => u.id);
        assignForm.employee_ids = assignForm.employee_ids.filter(id => !filteredIds.includes(id));
    }
};

const submitAssignment = () => {
    if(assignForm.employee_ids.length === 0) return alert('Vui lòng chọn ít nhất 1 học viên');
    assignForm.post(route('system.classes.assign-employees', props.courseClass.id), {
        onSuccess: () => {
            closeAssignModal();
        },
        preserveScroll: true
    });
};

// ==========================================
// LOGIC TÀI LIỆU
// ==========================================
const documents = computed(() => {
    return (props.courseClass.documents || []).map(doc => ({
        id: doc.id,
        name: doc.name,
        type: doc.type,
        date: new Date(doc.created_at).toLocaleDateString('vi-VN'),
        url: doc.url
    }));
});

const showAddDocModal = ref(false);
const docForm = useForm({
    name: '',
    type: 'file',
    file: null,
    url: ''
});

const submitAddDoc = () => {
    if (docForm.type !== 'link' && !docForm.file) return alert('Vui lòng chọn file tải lên!');
    if (docForm.type === 'link' && !docForm.url) return alert('Vui lòng nhập đường dẫn URL!');

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

// ==========================================
// LOGIC IMPORT/EXPORT EXCEL CHẤM ĐIỂM
// ==========================================
const showImportGradesModal = ref(false);
const importForm = useForm({
    excel_file: null,
});

const handleExportTemplate = () => {
    window.location.href = route('system.classes.export-grades', props.courseClass.id);
};

const submitImportGrades = () => {
    if (!importForm.excel_file) return alert('Vui lòng chọn file Excel kết quả chấm điểm!');

    importForm.post(route('system.classes.import-grades', props.courseClass.id), {
        onSuccess: () => {
            showImportGradesModal.value = false;
            importForm.reset();
            alert('Cập nhật điểm thành công!'); 
        },
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="'Chi tiết lớp học: ' + cls.code" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('system.classes.index')" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                <ChevronLeftIcon class="w-4 h-4" />
                Quay lại danh sách Lớp học
            </Link>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    
                    <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-md text-xs font-bold border border-blue-200">{{ cls.code }}</span>
                                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-md border uppercase" 
                                          :class="{
                                              'bg-green-50 text-green-700 border-green-200': cls.status === 'MỞ ĐĂNG KÝ', 
                                              'bg-yellow-50 text-yellow-700 border-yellow-200': cls.status === 'ĐANG HỌC', 
                                              'bg-blue-50 text-blue-700 border-blue-200': cls.status === 'KẾT THÚC',
                                              'bg-gray-100 text-gray-600 border-gray-200': cls.status === 'NHÁP'
                                          }">
                                        {{ cls.status }}
                                    </span>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-900 mt-2">{{ cls.name }}</h2>
                                <p class="text-sm text-gray-500 mt-1">Giảng viên: <span class="font-semibold text-gray-700">{{ cls.instructor }}</span> | Thời gian: <span class="font-semibold text-gray-700">{{ cls.time }}</span></p>
                            </div>
                            
                            <div class="flex gap-3">
                                <button v-if="cls.status === 'MỞ ĐĂNG KÝ'" @click="changeStatus('Đang học')" class="px-5 py-2 bg-green-600 text-white rounded-lg font-semibold text-sm hover:bg-green-700 transition shadow-sm shadow-green-600/30">
                                    BẮT ĐẦU HỌC
                                </button>
                                <button v-if="cls.status === 'ĐANG HỌC'" @click="changeStatus('Kết thúc')" class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition shadow-sm shadow-blue-600/30">
                                    KẾT THÚC LỚP
                                </button>
                                <button v-if="cls.status === 'KẾT THÚC'" @click="changeStatus('Mở đăng ký')" class="px-5 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-50 transition shadow-sm">
                                    MỞ LẠI LỚP NÀY
                                </button>
                                <button v-if="cls.status === 'NHÁP'" @click="changeStatus('Mở đăng ký')" class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition shadow-sm shadow-blue-600/30">
                                    XUẤT BẢN & MỞ ĐĂNG KÝ
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="px-8 border-b border-gray-200 bg-white">
                        <nav class="-mb-px flex space-x-8">
                            <button @click="activeTab = 'Thông tin'" :class="['whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors', activeTab === 'Thông tin' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">Thông tin chung</button>
                            <button @click="activeTab = 'Học viên'" :class="['whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2', activeTab === 'Học viên' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                                Học viên <span class="bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs font-mono">{{ students.length }} / {{ cls.max_students }}</span>
                            </button>
                            <button @click="activeTab = 'Tài liệu'" :class="['whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2', activeTab === 'Tài liệu' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                                Tài liệu <span class="bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs font-mono">{{ documents.length }}</span>
                            </button>
                            <button @click="activeTab = 'Kết quả'" :class="['whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors', activeTab === 'Kết quả' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">Kết quả & Điểm</button>
                        </nav>
                    </div>

                    <div class="p-8">
                        
                        <div v-if="activeTab === 'Thông tin'" class="max-w-3xl">
                            <div class="bg-gray-50/80 p-6 rounded-xl border border-gray-100">
                                <h3 class="font-bold text-gray-900 mb-6 uppercase text-sm tracking-wider">Cấu hình lớp học</h3>
                                <div class="grid grid-cols-2 gap-x-12 gap-y-6 text-sm">
                                    <div>
                                        <p class="text-gray-500 mb-1">Thuộc Khóa học</p>
                                        <p class="font-semibold text-gray-900">{{ props.courseClass.course?.name || '--' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-1">Ca học</p>
                                        <p class="font-semibold text-gray-900">{{ cls.shift }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-1">Phạm vi tuyển sinh</p>
                                        <p class="font-semibold text-gray-900">{{ cls.scope }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 mb-1">Sĩ số tối đa</p>
                                        <p class="font-semibold text-gray-900">{{ cls.max_students }} học viên</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'Học viên'">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-base font-bold text-gray-800">Danh sách học viên</h3>
                                <button @click="openAssignModal" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-blue-300 rounded-lg text-sm font-semibold text-blue-700 hover:bg-blue-50 shadow-sm transition-colors">
                                    <UserPlusIcon class="w-4 h-4 text-blue-600" /> Chỉ định Đào tạo
                                </button>
                            </div>
                            
                            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 text-left text-sm whitespace-nowrap">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Nhân viên</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Phòng ban</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Trạng thái Lớp</th>
                                            <th class="px-6 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-if="students.length === 0">
                                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic bg-gray-50/30">Lớp học này chưa có học viên nào.</td>
                                        </tr>
                                        <tr v-for="stu in students" :key="stu.id" class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 font-semibold text-gray-900">{{ stu.name }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ stu.department }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ stu.email }}</td>
                                            <td class="px-6 py-4 text-center font-semibold text-gray-800">{{ stu.status }}</td>
                                            <td class="px-6 py-4 text-right">
                                                <button @click="removeStudent(stu.id, stu.name)" class="text-red-600 hover:text-red-800 font-medium hover:underline">Gỡ bỏ</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div v-if="activeTab === 'Tài liệu'">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-base font-bold text-gray-800">Tài liệu riêng của Lớp</h3>
                                <button @click="showAddDocModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition-colors">
                                    <ArrowUpTrayIcon class="w-4 h-4 text-blue-600" /> Tải lên tài liệu
                                </button>
                            </div>
                            
                            <div class="overflow-x-auto border border-gray-200 rounded-lg max-w-4xl">
                                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tên tài liệu</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Loại</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày tải lên</th>
                                            <th class="px-6 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-if="documents.length === 0">
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 italic bg-gray-50/30">Chưa có tài liệu nào được đính kèm.</td>
                                        </tr>
                                        <tr v-for="doc in documents" :key="doc.id" class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <a :href="doc.url" target="_blank" :download="doc.type !== 'link' ? doc.name : false" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline flex items-center gap-2">
                                                    <DocumentTextIcon v-if="doc.type !== 'link'" class="w-4 h-4" />
                                                    <LinkIcon v-else class="w-4 h-4" />
                                                    {{ doc.name }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 text-gray-600 uppercase text-xs font-bold">{{ doc.type }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ doc.date }}</td>
                                            <td class="px-6 py-4 text-right">
                                                <button @click="deleteDoc(doc)" class="text-red-600 hover:underline font-medium">Xóa</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div v-if="activeTab === 'Kết quả'">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-base font-bold text-gray-800">Kết quả Học tập & Điểm số</h3>
                                <div class="flex gap-3">
                                    <a :href="route('system.classes.export-grades', props.courseClass.id)" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition-colors">
                                        <DocumentArrowDownIcon class="w-4 h-4 text-green-600" /> Xuất DS chấm điểm (Excel)
                                    </a>
                                    <button @click="showImportGradesModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-semibold text-white hover:bg-green-700 shadow-sm transition-colors">
                                        <ArrowUpTrayIcon class="w-4 h-4" /> Import Điểm Excel
                                    </button>
                                    <Link :href="route('system.grades.index')" class="text-sm text-blue-600 hover:underline font-semibold bg-blue-50 px-4 py-2 rounded-lg border border-blue-100 flex items-center">
                                        Trung tâm Chấm điểm &rarr;
                                    </Link>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 text-left text-sm whitespace-nowrap">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Học viên</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Phòng ban</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Tiến độ bài giảng</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Bài cuối khóa</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Điểm thi</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Kết quả Lớp</th>
                                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-if="resultsData.length === 0">
                                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 italic bg-gray-50/30">Lớp học này chưa có học viên nào tham gia.</td>
                                        </tr>
                                        <tr v-for="res in resultsData" :key="res.user_id" class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4">
                                                <p class="font-semibold text-gray-900">{{ res.name }}</p>
                                                <p class="text-xs text-gray-500">{{ res.emp_code }}</p>
                                            </td>
                                            <td class="px-6 py-4 text-gray-600">{{ res.department }}</td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="font-bold" :class="res.progress === 100 ? 'text-green-600' : 'text-blue-600'">{{ res.progress }}%</span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span v-if="!res.has_final" class="text-gray-400 text-xs italic">Không có test</span>
                                                <span v-else-if="res.submission_status === 'Chưa nộp'" class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded text-xs font-medium border border-gray-200">Chưa nộp</span>
                                                <span v-else-if="res.submission_status === 'Chờ chấm'" class="px-2.5 py-1 bg-yellow-50 text-yellow-700 rounded text-xs font-medium border border-yellow-200">Chờ chấm</span>
                                                <span v-else class="px-2.5 py-1 bg-green-50 text-green-700 rounded text-xs font-bold border border-green-200">Đã chấm</span>
                                            </td>
                                            <td class="px-6 py-4 font-bold text-gray-900 text-center">{{ res.score }}</td>
                                            <td class="px-6 py-4 text-center">
                                                <span v-if="res.class_status === 'completed'" class="text-green-600 font-bold text-xs uppercase bg-green-50 px-2 py-0.5 rounded">Đạt</span>
                                                <span v-else-if="res.class_status === 'failed'" class="text-red-600 font-bold text-xs uppercase bg-red-50 px-2 py-0.5 rounded">Trượt</span>
                                                <span v-else class="text-blue-600 font-bold text-xs uppercase bg-blue-50 px-2 py-0.5 rounded">Đang học</span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <Link v-if="res.submission_id && res.submission_status === 'Chờ chấm'" :href="route('system.grades.show', res.submission_id)" class="text-blue-600 hover:text-blue-800 font-bold hover:underline text-sm">Chấm bài</Link>
                                                <Link v-else-if="res.submission_id && res.submission_status === 'Đã chấm'" :href="route('system.grades.show', res.submission_id)" class="text-gray-500 hover:text-gray-800 font-medium hover:underline text-sm">Xem lại</Link>
                                                <span v-else class="text-gray-300 text-sm">--</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showingAssignModal" @close="closeAssignModal" maxWidth="3xl">
            <div class="bg-white rounded-xl overflow-hidden shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Chỉ định Đào tạo Thông minh</h3>
                    <button @click="closeAssignModal" class="text-gray-400 hover:text-gray-600 transition-colors"><XMarkIcon class="w-6 h-6" /></button>
                </div>
                
                <div class="p-6">
                    <div class="mb-4 bg-blue-50 text-blue-800 text-sm p-3 rounded-lg border border-blue-100">
                        <span class="font-bold">Đối tượng ưu tiên: </span> {{ props.courseClass.course?.target_audience || 'Toàn công ty' }}.
                        Hệ thống đã tự động lọc danh sách nhân sự phù hợp chưa tham gia lớp này.
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="relative">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tìm kiếm nhân sự</label>
                            <input v-model="searchQuery" type="text" placeholder="Tìm theo tên, email, chức danh..." class="w-full pl-10 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <MagnifyingGlassIcon class="w-4 h-4 text-gray-400 absolute left-3 top-[26px]" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Hạn chót hoàn thành (Bắt buộc) <span class="text-red-500">*</span></label>
                            <input v-model="assignForm.deadline" type="date" required class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                    </div>

                    <div v-if="isLoadingEmployees" class="py-10 text-center text-gray-500 italic">Đang tải danh sách nhân sự phù hợp...</div>
                    
                    <div v-else class="max-h-[350px] overflow-y-auto border border-gray-200 rounded-lg mb-6 shadow-inner relative">
                        <table class="min-w-full text-sm text-left whitespace-nowrap">
                            <thead class="bg-gray-100 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-4 py-3 w-10 text-center">
                                        <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    </th>
                                    <th class="px-4 py-3 font-bold text-gray-700">Nhân sự</th>
                                    <th class="px-4 py-3 font-bold text-gray-700">Phòng ban</th>
                                    <th class="px-4 py-3 font-bold text-gray-700">Chức danh / Vai trò</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-if="filteredUsers.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 italic bg-gray-50/50">
                                        Không tìm thấy nhân sự nào khớp điều kiện.
                                    </td>
                                </tr>
                                <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-blue-50/50 cursor-pointer transition-colors">
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" :value="user.id" v-model="assignForm.employee_ids" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-gray-900">{{ user.name }}</p>
                                        <p class="text-xs text-gray-500">{{ user.email }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 font-medium">{{ user.department?.name || '--' }}</td>
                                    <td class="px-4 py-3">
                                        <span v-if="user.is_manager" class="text-[10px] font-bold bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded uppercase">Quản lý</span>
                                        <span v-else class="text-[10px] font-bold bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded uppercase">Nhân viên</span>
                                        <p class="text-xs text-gray-600 mt-0.5">{{ user.job_title || '--' }}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-500">Đã chọn: <span class="font-bold text-blue-600">{{ assignForm.employee_ids.length }}</span> nhân sự</p>
                        <div class="flex gap-3">
                            <button @click="closeAssignModal" class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Hủy</button>
                            <button @click="submitAssignment" class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm shadow-blue-600/30 transition-colors">Giao việc Bắt buộc</button>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>

        <Modal :show="showAddDocModal" @close="showAddDocModal = false" maxWidth="md">
            <form @submit.prevent="submitAddDoc" class="bg-white rounded-xl overflow-hidden shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Tải lên tài liệu mới</h3>
                    <button type="button" @click="showAddDocModal = false" class="text-gray-400 hover:text-gray-600 transition-colors"><XMarkIcon class="w-6 h-6" /></button>
                </div>
                
                <div class="p-6">
                    <div class="space-y-5 mb-8">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tên hiển thị tài liệu <span class="text-red-500">*</span></label>
                            <input v-model="docForm.name" type="text" placeholder="Ví dụ: Bài tập thực hành buổi 1" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Loại đính kèm</label>
                            <select v-model="docForm.type" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm text-sm">
                                <option value="file">File Máy tính (PDF/Word/Excel...)</option>
                                <option value="link">Đường dẫn Link (Web/Drive)</option>
                            </select>
                        </div>

                        <div v-if="docForm.type === 'link'">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Đường dẫn URL <span class="text-red-500">*</span></label>
                            <input v-model="docForm.url" type="url" placeholder="https://..." class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm text-sm">
                        </div>

                        <div v-else>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Chọn File <span class="text-red-500">*</span></label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors relative cursor-pointer">
                                <input type="file" @input="docForm.file = $event.target.files[0]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <div class="space-y-1 text-center">
                                    <ArrowUpTrayIcon class="mx-auto h-8 w-8 text-gray-400" />
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <span class="font-medium text-blue-600">Tải file lên</span>
                                    </div>
                                    <p class="text-xs text-gray-500">{{ docForm.file ? docForm.file.name : 'PDF, DOC, XLS, PPT' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showAddDocModal = false" class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Hủy bỏ</button>
                        <button type="submit" :disabled="docForm.processing" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition-colors">
                            {{ docForm.processing ? 'Đang xử lý...' : 'Xác nhận tải lên' }}
                        </button>
                    </div>
                </div>
            </form>
        </Modal>

        <Modal :show="showImportGradesModal" @close="showImportGradesModal = false" maxWidth="md">
            <form @submit.prevent="submitImportGrades" class="bg-white rounded-xl overflow-hidden shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Import điểm từ Excel</h3>
                    <button type="button" @click="showImportGradesModal = false" class="text-gray-400 hover:text-gray-600 transition-colors"><XMarkIcon class="w-6 h-6" /></button>
                </div>
                
                <div class="p-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Chọn File Excel <span class="text-red-500">*</span></label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors relative cursor-pointer">
                            <input type="file" accept=".xlsx, .xls" @input="importForm.excel_file = $event.target.files[0]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="space-y-1 text-center">
                                <ArrowUpTrayIcon class="mx-auto h-8 w-8 text-green-500" />
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <span class="font-medium text-green-600">Tải file Excel lên</span>
                                </div>
                                <p class="text-xs text-gray-500">{{ importForm.excel_file ? importForm.excel_file.name : 'Chỉ hỗ trợ file .xlsx, .xls' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="showImportGradesModal = false" class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Hủy</button>
                        <button type="submit" :disabled="importForm.processing" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-sm transition-colors">
                            Cập nhật điểm
                        </button>
                    </div>
                </div>
            </form>
        </Modal>

    </AuthenticatedLayout>
</template>