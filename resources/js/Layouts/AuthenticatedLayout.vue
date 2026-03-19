<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Cấu hình Menu động theo đúng Mockup
const menuItems = computed(() => {
    // ROLE 2: ADMIN PHÒNG BAN
    if (user.value.role === 2) {
        return [
            { name: 'Trang chủ', route: 'dashboard', icon: '🏠' },
            { 
                name: 'Yêu cầu đào tạo', 
                icon: '📝',
                // Khai báo menu con
                subItems: [
                    { name: 'Gửi yêu cầu', route: 'department.requests.create' },
                    { name: 'Danh sách yêu cầu', route: 'department.requests.index' }
                ]
            },
            { name: 'Khóa học phòng ban', route: 'department.courses.index', icon: '📚' },
            { name: 'Danh sách nhân viên', route: 'department.employees.index', icon: '👥' },
        ];
    } 
    // ROLE 1: ADMIN HỆ THỐNG (Phòng Đào tạo)
    else if (user.value.role === 1) {
        return [
            { name: 'Trang chủ', route: 'dashboard', icon: '🏠' },
            { name: 'Quản lý Khóa học', route: 'dashboard', icon: '📖' },
            { name: 'Quản lý Lớp học', route: 'dashboard', icon: '🏫' },
            { name: 'Quản lý Giảng viên', route: 'dashboard', icon: '👨‍🏫' },
        ];
    }
    // ROLE 3: NHÂN VIÊN
    return [
        { name: 'Trang chủ', route: 'dashboard', icon: '🏠' },
        { name: 'Bàn học của tôi', route: 'dashboard', icon: '🎓' },
    ];
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        
        <aside class="w-64 bg-gray-900 text-white flex flex-col transition-all duration-300">
            <div class="h-16 flex items-center justify-center border-b border-gray-800">
                <span class="text-2xl font-extrabold text-white tracking-wider">LMS<span class="text-blue-500">PRO</span></span>
            </div>
            
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <template v-for="item in menuItems" :key="item.name">
                    
                    <div v-if="item.subItems" class="space-y-1">
                        <div class="flex items-center gap-3 px-4 py-3 text-gray-300 font-medium cursor-default">
                            <span class="text-lg">{{ item.icon }}</span>
                            <span>{{ item.name }}</span>
                            <span class="ml-auto text-xs">▼</span>
                        </div>
                        <div class="pl-12 space-y-1 border-l border-gray-700 ml-6">
                            <Link
                                v-for="sub in item.subItems"
                                :key="sub.name"
                                :href="route(sub.route)"
                                :class="[
                                    route().current(sub.route) ? 'text-blue-400 font-bold' : 'text-gray-400 hover:text-white',
                                    'block px-2 py-2 text-sm transition duration-200 relative'
                                ]"
                            >
                                <span v-if="route().current(sub.route)" class="absolute -left-[1.35rem] top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-blue-400"></span>
                                {{ sub.name }}
                            </Link>
                        </div>
                    </div>

                    <Link
                        v-else
                        :href="route(item.route)"
                        :class="[
                            route().current(item.route) ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-800 hover:text-white',
                            'flex items-center gap-3 px-4 py-3 rounded-md transition duration-200'
                        ]"
                    >
                        <span class="text-lg">{{ item.icon }}</span>
                        <span class="font-medium">{{ item.name }}</span>
                    </Link>

                </template>
            </nav>

            <div class="p-4 border-t border-gray-800 bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-xl">
                        🧑‍💻
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-bold truncate">{{ user.name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ user.department?.name || 'Hệ thống' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 z-10">
                <div>
                    <slot name="header" />
                </div>
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button class="flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 transition">
                            <span class="mr-2">Cài đặt tài khoản</span>
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </template>
                    <template #content>
                        <DropdownLink :href="route('profile.edit')"> Hồ sơ cá nhân </DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-600"> Đăng xuất </DropdownLink>
                    </template>
                </Dropdown>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                <slot />
            </main>
        </div>

    </div>
</template>