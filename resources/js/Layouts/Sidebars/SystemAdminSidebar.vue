<script setup>
import { Link } from '@inertiajs/vue3';
import {
    HomeIcon,
    InboxStackIcon,
    BookOpenIcon,
    AcademicCapIcon,
    UsersIcon,
    ChartPieIcon,
    ClipboardDocumentCheckIcon
} from '@heroicons/vue/24/outline';

// Hàm helper để check xem menu nào đang được chọn (Active)
const isActive = (routeName) => {
    return route().current(routeName + '*');
};
</script>

<template>
    <div class="px-2">
        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-3 mt-2 px-3">Hệ thống đào tạo</div>
        
        <nav class="space-y-1">
            <Link :href="route('dashboard')"
                  :class="['flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14px] font-medium transition-all duration-200',
                           route().current('dashboard') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']">
                <HomeIcon class="w-5 h-5" :class="route().current('dashboard') ? 'text-blue-600' : 'text-gray-400'" />
                Tổng quan
            </Link>

            <Link :href="route('system.requests.index')"
                  :class="['flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14px] font-medium transition-all duration-200',
                           isActive('system.requests') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']">
                <InboxStackIcon class="w-5 h-5" :class="isActive('system.requests') ? 'text-blue-600' : 'text-gray-400'" />
                Yêu cầu đào tạo
            </Link>

            <Link :href="route('system.courses.index')"
                  :class="['flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14px] font-medium transition-all duration-200',
                           isActive('system.courses') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']">
                <BookOpenIcon class="w-5 h-5" :class="isActive('system.courses') ? 'text-blue-600' : 'text-gray-400'" />
                Quản lý Khóa học
            </Link>

            <Link :href="route('system.classes.index')"
                  :class="['flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14px] font-medium transition-all duration-200',
                           isActive('system.classes') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']">
                <AcademicCapIcon class="w-5 h-5" :class="isActive('system.classes') ? 'text-blue-600' : 'text-gray-400'" />
                Quản lý Lớp học
            </Link>
        </nav>

        <div class="pt-6 pb-2">
            <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-3 mb-3">Nhân sự & Báo cáo</div>
        </div>

        <nav class="space-y-1">
            <Link :href="route('system.employees.index')"
                  :class="['flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14px] font-medium transition-all duration-200',
                           isActive('system.employees') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']">
                <UsersIcon class="w-5 h-5" :class="isActive('system.employees') ? 'text-blue-600' : 'text-gray-400'" />
                Danh sách Nhân viên
            </Link>

            <Link :href="route('system.grades.index')"
                  :class="['flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14px] font-medium transition-all duration-200',
                           isActive('system.grades') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']">
                <ClipboardDocumentCheckIcon class="w-5 h-5" :class="isActive('system.grades') ? 'text-blue-600' : 'text-gray-400'" />
                Điểm số & Kết quả
            </Link>

            <Link :href="route('system.reports.index')"
                  :class="['flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14px] font-medium transition-all duration-200',
                           isActive('system.reports') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']">
                <ChartPieIcon class="w-5 h-5" :class="isActive('system.reports') ? 'text-blue-600' : 'text-gray-400'" />
                Báo cáo thống kê
            </Link>
        </nav>
    </div>
</template>