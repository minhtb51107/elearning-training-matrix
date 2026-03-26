<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

import SystemAdminSidebar from '@/Layouts/Sidebars/SystemAdminSidebar.vue';
import DepartmentAdminSidebar from '@/Layouts/Sidebars/DepartmentAdminSidebar.vue';
import EmployeeSidebar from '@/Layouts/Sidebars/EmployeeSidebar.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// HỨNG DỮ LIỆU THÔNG BÁO TỪ MIDDLEWARE
const notifications = computed(() => page.props.auth.notifications || []);
const unreadCount = computed(() => page.props.auth.unread_count || 0);

const showNotifications = ref(false);
const toggleNotifications = () => showNotifications.value = !showNotifications.value;

const closeNotifications = (e) => {
    if (showNotifications.value && !e.target.closest('.notification-container')) {
        showNotifications.value = false;
    }
};
onMounted(() => document.addEventListener('click', closeNotifications));
onUnmounted(() => document.removeEventListener('click', closeNotifications));

// GỌI API ĐÁNH DẤU ĐÃ ĐỌC
const markAsRead = (id) => {
    router.post(route('notifications.read', id), {}, { preserveScroll: true });
};

const markAllAsRead = () => {
    router.post(route('notifications.read-all'), {}, { preserveScroll: true });
};
</script>

<template>
    <div class="min-h-screen bg-[#f8fafc] flex font-sans antialiased text-gray-900">
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col transition-all duration-300 z-20">
            <div class="h-16 flex items-center justify-center border-b border-gray-100">
                <span class="text-2xl font-extrabold text-gray-900 tracking-wider">LMS<span class="text-blue-600">PRO</span></span>
            </div>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar px-3 py-6">
                <SystemAdminSidebar v-if="user.role === 1" />
                <DepartmentAdminSidebar v-else-if="user.role === 2" />
                <EmployeeSidebar v-else />
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 border-b border-gray-200 z-10 relative">
                <div class="flex items-center gap-4">
                    <slot name="header" />
                </div>
                <div class="flex items-center gap-6">
                    <div class="relative notification-container">
                        <button @click="toggleNotifications" class="text-gray-600 hover:text-gray-900 relative focus:outline-none flex items-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span class="ml-1 text-xs">▼</span>
                            <span v-if="unreadCount > 0" class="absolute top-0 right-3 flex items-center justify-center h-4 w-4 rounded-full bg-red-500 text-white text-[10px] font-bold ring-2 ring-white">
                                {{ unreadCount > 9 ? '9+' : unreadCount }}
                            </span>
                        </button>

                        <div v-if="showNotifications" class="absolute right-0 mt-3 w-80 bg-white border border-gray-300 shadow-xl rounded-sm z-50">
                            <div class="flex justify-between items-center px-4 py-3 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                    <h3 class="text-base font-bold text-gray-800 uppercase">THÔNG BÁO</h3>
                                </div>
                                <button @click="showNotifications = false" class="text-red-600 font-bold hover:text-red-800">[ X ]</button>
                            </div>
                            
                            <div class="max-h-80 overflow-y-auto">
                                <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-500 italic">
                                    Bạn chưa có thông báo nào.
                                </div>
                                
                                <div v-for="noti in notifications" :key="noti.id" @click="markAsRead(noti.id)" class="px-4 py-3 border-b border-gray-100 flex gap-3 hover:bg-blue-50 cursor-pointer transition" :class="{'bg-gray-50': noti.read_at}">
                                    <div class="mt-1.5 flex-shrink-0">
                                        <div v-if="!noti.read_at" class="w-2 h-2 rounded-full bg-[#d97706] ring-2 ring-orange-200"></div>
                                        <div v-else class="w-2 h-2 rounded-full border-2 border-gray-300"></div>
                                    </div>
                                    <div>
                                        <p class="text-[13px] text-gray-900 font-semibold mb-0.5">{{ noti.data.title }}</p>
                                        <p class="text-[12px] text-gray-700" v-html="noti.data.message"></p>
                                        <p class="text-[10px] text-gray-400 mt-1">{{ new Date(noti.created_at).toLocaleString('vi-VN') }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="unreadCount > 0" class="px-4 py-2 border-t border-gray-200 text-center">
                                <button @click="markAllAsRead" class="text-xs font-bold text-blue-600 hover:underline">Đánh dấu tất cả đã đọc</button>
                            </div>
                        </div>
                    </div>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center text-sm font-bold text-gray-700 hover:text-blue-600 transition focus:outline-none">
                                <span class="mr-2">Cài đặt tài khoản</span>
                                <svg class="fill-current h-4 w-4" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')"> Hồ sơ cá nhân </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-600 font-bold"> Đăng xuất </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-white p-8">
                <slot />
            </main>
        </div>
    </div>
</template>