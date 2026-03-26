<script setup>
import { watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeftIcon, PlusCircleIcon, XMarkIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    courseClass: Object,
    courses: Array,
    departments: Array,
    instructors: Array
});

// Parse dữ liệu sessions từ DB
const initialSessions = props.courseClass.sessions ? props.courseClass.sessions.map(s => ({
    id: s.id,
    date: s.date ? s.date.split('T')[0] : '',
    start_time: s.start_time || '08:00',
    end_time: s.end_time || '10:00',
    format: s.format || 'Online',
    link: s.link || '',
    room: s.room || '',
    content: s.content || '',
    is_existing: true
})) : [];

const form = useForm({
    course_id: props.courseClass.course_id || '',
    name: props.courseClass.name || '',
    instructor_id: props.courseClass.instructor_id || '',
    duration: props.courseClass.course?.duration || '', 
    start_date: props.courseClass.start_date ? props.courseClass.start_date.split('T')[0] : '',
    end_date: props.courseClass.end_date ? props.courseClass.end_date.split('T')[0] : '',
    shift: props.courseClass.shift || 'Sáng',
    department_id: props.courseClass.department_id || '',
    max_students: props.courseClass.max_students || '',
    format: props.courseClass.format || 'Offline',
    roles: props.courseClass.roles || 'Team lead',
    action: 'published',
    
    // MẢNG QUẢN LÝ LỊCH HỌC
    sessions: initialSessions,
    deleted_session_ids: []
});

watch(() => form.course_id, (newCourseId) => {
    const course = props.courses.find(c => c.id === newCourseId);
    if (course) {
        form.duration = course.duration;
        form.format = course.format || 'Offline';
    }
});

// HÀM QUẢN LÝ BUỔI HỌC
const addSession = () => {
    form.sessions.push({
        date: '',
        start_time: '08:00',
        end_time: '10:00',
        format: form.format || 'Offline',
        link: '',
        room: '',
        content: '',
        is_existing: false
    });
};

const removeSession = (index) => {
    const sessionToRemove = form.sessions[index];
    if(confirm('Xóa buổi học này khỏi lịch học?')) {
        if (sessionToRemove.is_existing && sessionToRemove.id) {
            form.deleted_session_ids.push(sessionToRemove.id);
        }
        form.sessions.splice(index, 1);
    }
};

const submitForm = (actionType) => {
    form.action = actionType;
    form.put(route('system.classes.update', props.courseClass.id), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="'Sửa lớp học: ' + courseClass.code" />

    <AuthenticatedLayout>
        <template #header>
            <Link :href="route('system.classes.index')" class="flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors">
                <ChevronLeftIcon class="w-4 h-4" />
                Quay lại danh sách
            </Link>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-8 sm:p-10 border border-gray-200">
                    
                    <div class="mb-8 flex justify-between items-end border-b border-gray-100 pb-5">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Cập nhật lớp học</h2>
                            <p class="text-sm text-gray-500 mt-1">Chỉnh sửa thông tin lịch học và giảng viên.</p>
                        </div>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-md font-bold border border-blue-200 shadow-sm">{{ courseClass.code }}</span>
                    </div>

                    <form @submit.prevent>
                        
                        <div class="flex flex-col items-center justify-center gap-2 mb-10 bg-gray-50/50 p-6 rounded-xl border border-gray-100">
                            <div class="flex flex-col items-center justify-center gap-3 w-full">
                                <label class="text-sm font-bold text-gray-900 uppercase tracking-wider">Khóa học của lớp này</label>
                                <select v-model="form.course_id" class="w-full max-w-md border-gray-300 rounded-lg shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500">
                                    <option v-for="course in courses" :key="course.id" :value="course.id">
                                        {{ course.code }} - {{ course.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">1. Thông tin tổng quan lớp học</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Mã lớp học</label>
                                    <input type="text" disabled :value="courseClass.code" class="w-full bg-gray-100 border-gray-200 rounded-lg text-gray-600 text-sm cursor-not-allowed shadow-sm font-bold">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Giảng viên <span class="text-red-500">*</span></label>
                                    <select v-model="form.instructor_id" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500">
                                        <option value="">-- Chưa phân công --</option>
                                        <option v-for="inst in instructors" :key="inst.id" :value="inst.id">{{ inst.name }}</option>
                                    </select>
                                    <div v-if="form.errors.instructor_id" class="text-red-500 text-xs mt-1">{{ form.errors.instructor_id }}</div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Tên lớp học <span class="text-red-500">*</span></label>
                                    <input v-model="form.name" type="text" required class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500">
                                    <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Ca học <span class="text-red-500">*</span></label>
                                    <select v-model="form.shift" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                        <option value="Sáng">Sáng (08:00 - 12:00)</option>
                                        <option value="Chiều">Chiều (13:30 - 17:30)</option>
                                        <option value="Tối">Tối (18:00 - 21:00)</option>
                                        <option value="Cả ngày">Cả ngày</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2 p-4 bg-gray-50/50 rounded-lg border border-gray-100">
                                    <label class="block text-sm font-bold text-gray-700 mb-3">Thời gian diễn ra khóa học:</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                        <div>
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-medium text-gray-500">Từ ngày <span class="text-red-500">*</span></span>
                                                <input v-model="form.start_date" type="date" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500 text-gray-700">
                                            </div>
                                            <div v-if="form.errors.start_date" class="text-red-500 text-xs mt-1">{{ form.errors.start_date }}</div>
                                        </div>
                                        <div>
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-medium text-gray-500">Đến ngày <span class="text-red-500">*</span></span>
                                                <input v-model="form.end_date" type="date" :min="form.start_date" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500 text-gray-700">
                                            </div>
                                            <div v-if="form.errors.end_date" class="text-red-500 text-xs mt-1">{{ form.errors.end_date }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">2. Phạm vi & Đối tượng</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phòng ban (Áp dụng)</label>
                                    <select v-model="form.department_id" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500">
                                        <option value="">-- Toàn công ty --</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                    </select>
                                    <div v-if="form.errors.department_id" class="text-red-500 text-xs mt-1">{{ form.errors.department_id }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Sĩ số tối đa <span class="text-red-500">*</span></label>
                                    <input v-model="form.max_students" type="number" min="1" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Hình thức</label>
                                    <select v-model="form.format" class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:ring-amber-500 focus:border-amber-500">
                                        <option value="Offline">Offline (Tại lớp)</option>
                                        <option value="Online">Online (Zoom/Teams)</option>
                                        <option value="Hybrid">Hybrid (Kết hợp)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-10">
                            <div class="flex justify-between items-end mb-6">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider">3. Lịch học chi tiết (Các buổi học)</h3>
                                <button type="button" @click="addSession" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 rounded-lg text-sm font-bold transition-colors">
                                    <PlusCircleIcon class="w-5 h-5" /> Thêm Buổi học
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                <div v-if="form.sessions.length === 0" class="p-6 bg-gray-50 border border-gray-200 border-dashed rounded-xl text-center text-sm text-gray-500 italic">
                                    Lớp học này chưa được xếp lịch từng buổi. Bấm "Thêm Buổi học" để tạo lịch.
                                </div>
                                
                                <div v-for="(session, index) in form.sessions" :key="index" :class="['p-5 border rounded-xl relative group shadow-sm transition hover:border-amber-300', session.is_existing ? 'bg-white border-gray-200' : 'bg-amber-50/30 border-amber-100']">
                                    <div class="flex items-center gap-2 mb-4 pb-3" :class="session.is_existing ? 'border-b border-gray-100' : 'border-b border-amber-100'">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold" :class="session.is_existing ? 'bg-gray-600 text-white' : 'bg-amber-500 text-white'">{{ index + 1 }}</div>
                                        <span class="font-bold text-sm text-gray-900">Buổi học {{ index + 1 }}</span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                                        <div class="md:col-span-4">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ngày học <span class="text-red-500">*</span></label>
                                            <input v-model="session.date" type="date" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Giờ bắt đầu <span class="text-red-500">*</span></label>
                                            <input v-model="session.start_time" type="time" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                        </div>
                                        <div class="md:col-span-4">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Giờ kết thúc <span class="text-red-500">*</span></label>
                                            <input v-model="session.end_time" type="time" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                                        <div class="md:col-span-4">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Hình thức <span class="text-red-500">*</span></label>
                                            <select v-model="session.format" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                                <option value="Online">Online</option>
                                                <option value="Offline">Offline</option>
                                            </select>
                                        </div>
                                        <div class="md:col-span-8" v-if="session.format === 'Online'">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Link học (Zoom/Meet/Teams)</label>
                                            <input v-model="session.link" type="url" placeholder="https://..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                        </div>
                                        <div class="md:col-span-8" v-if="session.format === 'Offline'">
                                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Phòng học (Offline)</label>
                                            <input v-model="session.room" type="text" placeholder="VD: Phòng 101, Tầng 2..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nội dung buổi học / Ghi chú thêm</label>
                                        <textarea v-model="session.content" rows="2" placeholder="Ghi chú những nội dung cần chuẩn bị cho buổi học..." class="w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm resize-none"></textarea>
                                    </div>
                                    
                                    <button type="button" @click="removeSession(index)" class="absolute -right-2 -top-2 w-7 h-7 bg-white border border-gray-200 text-gray-400 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all shadow-sm z-10" title="Xóa buổi này">
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-100">
                            <Link :href="route('system.classes.index')" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-semibold transition-colors shadow-sm">
                                Hủy bỏ
                            </Link>
                            <button @click="submitForm(courseClass.status === 'Nháp' ? 'draft' : 'published')" :disabled="form.processing" type="button" class="px-8 py-2.5 bg-amber-500 text-white hover:bg-amber-600 rounded-lg font-semibold transition-colors shadow-sm shadow-amber-500/30 disabled:opacity-50 flex items-center gap-2">
                                <span v-if="form.processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                                Lưu thông tin Cập nhật
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>