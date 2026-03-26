<script setup>
import { Link } from '@inertiajs/vue3';
// 1. Import các Icon cần dùng từ thư viện Heroicons (dạng outline nét mảnh)
import { 
    ChartBarIcon, 
    DocumentTextIcon, 
    BookOpenIcon, 
    UserGroupIcon 
} from '@heroicons/vue/24/outline';

// 2. Gán thẳng Component của Icon vào biến icon thay vì dùng chuỗi Emoji
const menuItems = [
    { name: 'Trang chủ', route: 'dashboard', icon: ChartBarIcon },
    { name: 'Yêu cầu đào tạo', route: 'department.requests.index', icon: DocumentTextIcon },
    { name: 'Khóa học', route: 'department.courses.index', icon: BookOpenIcon },
    { name: 'Nhân viên', route: 'department.employees.index', icon: UserGroupIcon },
];
</script>

<template>
    <nav class="space-y-1.5">
        <template v-for="item in menuItems" :key="item.name">
            <Link :href="route(item.route)" 
                :class="[
                    route().current(item.route) || (item.route === 'department.requests.index' && route().current('department.requests.create'))
                        ? 'bg-blue-50 text-blue-700 font-bold ring-1 ring-blue-600/20 shadow-sm' 
                        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 font-medium', 
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group'
                ]">
                
                <span :class="[
                        route().current(item.route) || (item.route === 'department.requests.index' && route().current('department.requests.create'))
                            ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600',
                        'transition-transform duration-200 group-hover:scale-110 flex items-center justify-center'
                    ]">
                    <component :is="item.icon" class="w-5 h-5 stroke-2" />
                </span>
                
                <span class="text-[14px]">{{ item.name }}</span>
            </Link>
        </template>
    </nav>
</template>

<style scoped>
/* Tinh chỉnh thanh cuộn cho hợp với nền sáng */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1; /* Xám nhạt */
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}
</style>