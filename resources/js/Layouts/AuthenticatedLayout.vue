<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const showNotifications = ref(false);
const toggleNotifications = () => showNotifications.value = !showNotifications.value;

const closeNotifications = (e) => {
    if (showNotifications.value && !e.target.closest('.notification-container')) {
        showNotifications.value = false;
    }
};
onMounted(() => document.addEventListener('click', closeNotifications));
onUnmounted(() => document.removeEventListener('click', closeNotifications));

const notifications = ref([
    { id: 1, text: 'Lớp <strong>"Sales nâng cao - L1"</strong> sắp bắt đầu<br><span class="text-xs text-gray-500">Thời gian: 09:00 – 20/03/2026</span>', is_read: false },
    { id: 2, text: 'Bạn đã được duyệt tham gia khóa học<br><strong class="text-[#d97706]">"Kỹ năng bán hàng chuyên sâu"</strong>', is_read: false },
    { id: 3, text: 'Bài kiểm tra khóa <strong class="text-[#16a34a]">"Quy trình nội bộ"</strong><br>đã được chấm', is_read: true },
    { id: 4, text: 'Bạn đã hoàn thành khóa học<br><strong class="text-[#0ea5e9]">"Sales cơ bản"</strong>', is_read: true },
]);

const menuItems = computed(() => {
    if (user.value.role === 1) {
        return [
            { name: 'Trang chủ', route: 'dashboard', icon: '🏠' },
            { name: 'Danh sách yêu cầu đào tạo', route: 'system.requests.index', icon: '📋' },
            { name: 'Khóa học', icon: '📖', subItems: [ { name: 'Quản lý khóa học', route: 'system.courses.index' }, { name: 'Tạo khóa học', route: 'system.courses.create' } ] },
            { name: 'Lớp học', icon: '🏫', subItems: [ { name: 'Quản lý lớp học', route: 'system.classes.index' }, { name: 'Chấm bài', route: 'system.grades.index' }, { name: 'Tạo lớp học', route: 'system.classes.create' } ] },
            { name: 'Báo cáo - KPI', route: 'system.reports.index', icon: '📊' },
            { name: 'Danh sách nhân viên', route: 'system.employees.index', icon: '👥' },
        ];
    } else if (user.value.role === 2) {
        return [
            { name: 'Trang chủ', route: 'dashboard', icon: '🏠' },
            { name: 'Yêu cầu đào tạo', icon: '📝', subItems: [ { name: 'Gửi yêu cầu', route: 'department.requests.create' }, { name: 'Danh sách yêu cầu', route: 'department.requests.index' } ] },
            { name: 'Khóa học phòng ban', route: 'department.courses.index', icon: '📚' },
            { name: 'Danh sách nhân viên', route: 'department.employees.index', icon: '👥' },
        ];
    }
    // ROLE 3: NHÂN VIÊN
    return [
        { name: 'Trang chủ', route: 'dashboard', icon: '🏠' },
        { name: 'Khóa học', icon: '📚', subItems: [ { name: 'Danh sách khóa học', route: 'employee.courses.index' } ] },
        { name: 'Lớp học', icon: '🏫', subItems: [ { name: 'Lớp học của tôi', route: 'employee.my-classes' }, { name: 'Lịch học của tôi', route: 'employee.my-schedule' } ] },
        { name: 'Kết quả', icon: '🏆', subItems: [ { name: 'Kết quả & Chứng chỉ', route: 'employee.results' } ] }, // ĐÃ NỐI LINK
        { name: 'Tài khoản', route: 'employee.account', icon: '👤' }, // ĐÃ NỐI LINK
    ];
});
</script>

<template>
    <div class="min-h-screen bg-[#f8fafc] flex font-sans antialiased text-gray-900">
        <aside class="w-64 bg-gray-900 text-white flex flex-col transition-all duration-300 shadow-xl z-20">
            <div class="h-16 flex items-center justify-center border-b border-gray-800">
                <span class="text-2xl font-extrabold text-white tracking-wider">LMS<span class="text-blue-500">PRO</span></span>
            </div>
            
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <template v-for="item in menuItems" :key="item.name">
                    <div v-if="item.subItems" class="space-y-1">
                        <div class="flex items-center gap-3 px-4 py-3 text-gray-400 font-bold cursor-default uppercase text-xs tracking-widest opacity-80">
                            <span class="text-lg">{{ item.icon }}</span>
                            <span>{{ item.name }}</span>
                            <span class="ml-auto text-[10px]">▼</span>
                        </div>
                        <div class="pl-12 space-y-1 border-l border-gray-700 ml-6">
                            <Link v-for="sub in item.subItems" :key="sub.name" :href="route(sub.route)" :class="[route().current(sub.route) ? 'text-blue-400 font-bold' : 'text-gray-400 hover:text-white', 'block px-2 py-2 text-sm transition duration-200 relative']">
                                <span v-if="route().current(sub.route)" class="absolute -left-[1.35rem] top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-blue-400 shadow-[0_0_8px_rgba(96,165,250,0.8)]"></span>
                                {{ sub.name }}
                            </Link>
                        </div>
                    </div>
                    <Link v-else :href="route(item.route)" :class="[route().current(item.route) ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-800 hover:text-white font-medium', 'flex items-center gap-3 px-4 py-3 rounded-md transition duration-200']">
                        <span class="text-lg">{{ item.icon }}</span>
                        <span>{{ item.name }}</span>
                    </Link>
                </template>
            </nav>
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
                            <span class="absolute top-0 right-3 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
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
                                <div v-for="noti in notifications" :key="noti.id" class="px-4 py-3 border-b border-gray-100 flex gap-3 hover:bg-gray-50 cursor-pointer transition">
                                    <div class="mt-1.5 flex-shrink-0">
                                        <div v-if="!noti.is_read" class="w-2 h-2 rounded-full bg-[#d97706] ring-2 ring-orange-200"></div>
                                        <div v-else class="w-2 h-2 rounded-full border-2 border-gray-300"></div>
                                    </div>
                                    <p class="text-[13px] text-gray-700" v-html="noti.text"></p>
                                </div>
                            </div>
                            <div class="px-4 py-2 border-t border-gray-200 text-xs text-gray-500">
                                Hiển thị 4/12 thông báo
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