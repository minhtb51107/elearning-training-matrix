<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    course: Object,
    classes: Array,
    instructors: Array
});

// Quản lý trạng thái Modal (Bật/Tắt)
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    code: '', name: '', instructor_id: '', instructor_name: '',
    capacity: 50, start_date: '', end_date: '', status: 'draft'
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (cls) => {
    isEditing.value = true;
    editingId.value = cls.id;
    form.clearErrors();
    form.code = cls.code; form.name = cls.name; form.instructor_id = cls.instructor_id || '';
    form.instructor_name = cls.instructor_name || ''; form.capacity = cls.capacity;
    form.start_date = cls.start_date; form.end_date = cls.end_date; form.status = cls.status;
    isModalOpen.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.classes.update', editingId.value), { onSuccess: () => isModalOpen.value = false });
    } else {
        form.post(route('admin.courses.classes.store', props.course.id), { onSuccess: () => isModalOpen.value = false });
    }
};

const deleteClass = (id, name) => {
    if (confirm(`Xóa lớp "${name}"? Hành động này không thể hoàn tác.`)) {
        router.delete(route('admin.classes.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="'Lớp học: ' + course.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Khóa học: <span class="text-indigo-600">{{ course.name }}</span> > Danh sách Lớp
                </h2>
                <div>
                    <Link :href="route('admin.courses.index')" class="text-gray-600 hover:text-gray-900 mr-4">&larr; Quay lại</Link>
                    <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                        + Tạo lớp mới
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 pb-0" v-if="$page.props.flash.success">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    <p class="font-bold">Thành công!</p><p>{{ $page.props.flash.success }}</p>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mã Lớp</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tên Lớp</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thời gian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sĩ số</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="classes.length === 0">
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">Chưa có lớp học nào.</td>
                            </tr>
                            <tr v-for="cls in classes" :key="cls.id">
                                <td class="px-6 py-4 font-bold">{{ cls.code }}</td>
                                <td class="px-6 py-4">
                                    <div>{{ cls.name }}</div>
                                    <div class="text-xs text-gray-500">GV: {{ cls.instructor?.name || cls.instructor_name || 'Chưa xếp' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ cls.start_date }} <br>&rarr; {{ cls.end_date }}</td>
                                <td class="px-6 py-4 text-sm">{{ cls.capacity }} học viên</td>
                                <td class="px-6 py-4 text-sm capitalize">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold" 
                                        :class="{'bg-gray-200 text-gray-800': cls.status==='draft', 'bg-blue-100 text-blue-800': cls.status==='open', 'bg-yellow-100 text-yellow-800': cls.status==='in_progress', 'bg-green-100 text-green-800': cls.status==='completed'}">
                                        {{ cls.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <button @click="openEditModal(cls)" class="text-blue-600 hover:text-blue-900 mr-3">Sửa</button>
                                    <button @click="deleteClass(cls.id, cls.name)" class="text-red-600 hover:text-red-900">Xóa</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-6">
                <h3 class="text-lg font-bold mb-4">{{ isEditing ? 'Cập nhật Lớp học' : 'Tạo Lớp học mới' }}</h3>
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel value="Mã Lớp (*)" />
                            <TextInput type="text" v-model="form.code" class="w-full mt-1" placeholder="VD: LOP-01" />
                            <InputError :message="form.errors.code" />
                        </div>
                        <div>
                            <InputLabel value="Tên Lớp (*)" />
                            <TextInput type="text" v-model="form.name" class="w-full mt-1" />
                            <InputError :message="form.errors.name" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <InputLabel value="Ngày bắt đầu (*)" />
                            <TextInput type="date" v-model="form.start_date" class="w-full mt-1" />
                            <InputError :message="form.errors.start_date" />
                        </div>
                        <div>
                            <InputLabel value="Ngày kết thúc (*)" />
                            <TextInput type="date" v-model="form.end_date" class="w-full mt-1" />
                            <InputError :message="form.errors.end_date" />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <InputLabel value="Sĩ số tối đa (*)" />
                            <TextInput type="number" v-model="form.capacity" class="w-full mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Giảng viên nội bộ" />
                            <select v-model="form.instructor_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                <option value="">-- Chọn --</option>
                                <option v-for="user in instructors" :value="user.id" :key="user.id">{{ user.name }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Hoặc GV thuê ngoài" />
                            <TextInput type="text" v-model="form.instructor_name" class="w-full mt-1" placeholder="Nhập tên..." />
                        </div>
                    </div>
                    <div class="mb-4 w-1/2">
                        <InputLabel value="Trạng thái" />
                        <select v-model="form.status" class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            <option value="draft">Bản nháp (Draft)</option>
                            <option value="open">Mở đăng ký (Open)</option>
                            <option value="in_progress">Đang học (In Progress)</option>
                            <option value="completed">Đã kết thúc (Completed)</option>
                        </select>
                    </div>
                    <div class="flex justify-end border-t pt-4">
                        <button type="button" @click="isModalOpen = false" class="mr-3 px-4 py-2 text-gray-600 bg-gray-200 rounded">Hủy</button>
                        <PrimaryButton :disabled="form.processing">Lưu dữ liệu</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>