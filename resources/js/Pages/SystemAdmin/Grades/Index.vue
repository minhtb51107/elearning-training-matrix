<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    submissions: Object
});
</script>

<template>
    <Head title="Đánh giá - Chấm bài" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                    <span class="font-bold">{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 mb-8 border-b pb-4 uppercase">Đánh giá</h2>

                    <h3 class="text-base font-bold text-gray-800 mb-3">Danh sách bài chờ chấm ({{ submissions.total }} bài):</h3>
                    <div class="overflow-x-auto border border-gray-300">
                        <table class="min-w-full divide-y divide-gray-300 text-center text-[13px]">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300 w-12">STT</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Mã NV</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Họ tên</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Phòng ban</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Khóa học</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Lớp</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Loại bài</th>
                                    <th class="px-3 py-3 font-bold text-gray-900 border-r border-gray-300">Ngày nộp</th>
                                    <th class="px-3 py-3 font-bold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-300">
                                <tr v-if="submissions.data.length === 0">
                                    <td colspan="9" class="px-3 py-8 text-gray-500 italic">Hiện không có bài nào đang chờ chấm.</td>
                                </tr>
                                <tr v-for="item in submissions.data" :key="item.id" class="hover:bg-gray-50 transition cursor-pointer" @click="router.get(route('system.grades.show', item.id))">
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.stt }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.emp_code }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-800 font-medium text-left">{{ item.name }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700 text-left">{{ item.department }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-800 text-left">{{ item.course }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.class_code }}</td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">
                                        <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded text-xs font-bold">{{ item.type }}</span>
                                    </td>
                                    <td class="px-3 py-3 border-r border-gray-300 text-gray-700">{{ item.submit_date }}</td>
                                    <td class="px-3 py-3">
                                        <Link @click.stop :href="route('system.grades.show', item.id)" class="text-[#0ea5e9] hover:underline font-bold text-sm">
                                            [ Chấm ]
                                        </Link>
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