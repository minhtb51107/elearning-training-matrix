<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3'; // Import thêm router để gọi API xóa

defineProps({
    courses: Array,
});

// Hàm xử lý Xóa với cảnh báo an toàn
const deleteCourse = (id, name) => {
    if (confirm(`Bạn có chắc chắn muốn xóa khóa học "${name}" không? Hành động này không thể hoàn tác!`)) {
        router.delete(route('admin.courses.destroy', id), {
            preserveScroll: true // Xóa xong giữ nguyên vị trí cuộn chuột, không bị nhảy lên đầu trang
        });
    }
};
</script>

<template>
    <Head title="Quản lý khóa học" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Quản lý khóa học (Phòng Đào tạo)</h2>
                
                <Link :href="route('admin.courses.create')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                    + Tạo khóa học mới
                </Link>
            </div>
        </template>

        <div class="py-6 pb-0" v-if="$page.props.flash.success">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">
                    <p class="font-bold">Thành công!</p>
                    <p>{{ $page.props.flash.success }}</p>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-4 flex justify-between items-center bg-white p-4 rounded-lg shadow-sm">
                    <div class="space-x-4 text-sm font-medium text-gray-600">
                        <span class="text-blue-600 border-b-2 border-blue-600 pb-1 cursor-pointer">Tất cả</span>
                        <span class="hover:text-blue-600 cursor-pointer">Chưa có lớp</span>
                        <span class="hover:text-blue-600 cursor-pointer">Đang triển khai</span>
                        <span class="hover:text-blue-600 cursor-pointer">Đã kết thúc</span>
                    </div>
                    <div>
                        <input type="text" placeholder="Tìm kiếm khóa học..." class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" />
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã KH</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên khóa học</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hình thức</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="courses.length === 0">
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        Chưa có khóa học nào trên hệ thống. Hãy tạo mới!
                                    </td>
                                </tr>
                                
                                <tr v-for="course in courses" :key="course.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ course.code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ course.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 capitalize">{{ course.format }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span v-if="course.is_mandatory" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Bắt buộc
                                        </span>
                                        <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Tự chọn
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('admin.courses.classes.index', course.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Quản lý Lớp
                                        </Link>
                                        
                                        <Link :href="route('admin.courses.edit', course.id)" class="text-blue-600 hover:text-blue-900 mr-3">
                                            Sửa
                                        </Link>
                                        
                                        <button @click="deleteCourse(course.id, course.name)" class="text-red-600 hover:text-red-900">
                                            Xóa
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>