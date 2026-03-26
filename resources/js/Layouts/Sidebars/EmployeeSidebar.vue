<script setup>
import { Link } from '@inertiajs/vue3';
import {
    HomeIcon,
    BookOpenIcon,
    AcademicCapIcon,
    CalendarDaysIcon,
    CheckBadgeIcon,
    UserCircleIcon
} from '@heroicons/vue/24/outline';

// Hàm helper để check xem menu nào đang được chọn (Active)
const isActive = (routeName) => {
    return route().current(routeName) || route().current(routeName + '.*');
};
</script>

<template>
    <div class="px-2 py-2">
        
        <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-3 mt-2 px-3">Tổng quan</div>
        <nav class="space-y-1">
            <Link :href="route('dashboard')"
                  :class="['flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14px] font-medium transition-all duration-200',
                           route().current('dashboard') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']">
                <HomeIcon class="w-5 h-5" :class="route().current('dashboard') ? 'text-blue-600' : 'text-gray-400'" />
                Trang chủ
            </Link>
        </nav>

        <div class="pt-6 pb-2">
            <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-3 mb-3">Học tập & Đào tạo</div>
        </div>
        <nav class="space-y-1">
            <Link :href="route('employee.courses.index')"
                  :class="['flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14px] font-medium transition-all duration-200',
                           isActive('employee.courses') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']">
                <BookOpenIcon class="w-5 h-5" :class="isActive('employee.courses') ? 'text-blue-600' : 'text-gray-400'" />
                Danh sách Khóa học
            </Link>

            <Link :href="route('employee.my-classes')"
                  :class="['flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14px] font-medium transition-all duration-200',
                           isActive('employee.my-classes') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']">
                <AcademicCapIcon class="w-5 h-5" :class="isActive('employee.my-classes') ? 'text-blue-600' : 'text-gray-400'" />
                Lớp học của tôi
            </Link>

            <Link :href="route('employee.my-schedule')"
                  :class="['flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14px] font-medium transition-all duration-200',
                           isActive('employee.my-schedule') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']">
                <CalendarDaysIcon class="w-5 h-5" :class="isActive('employee.my-schedule') ? 'text-blue-600' : 'text-gray-400'" />
                Lịch học của tôi
            </Link>
        </nav>

        <div class="pt-6 pb-2">
            <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-3 mb-3">Cá nhân</div>
        </div>
        <nav class="space-y-1">
            <Link :href="route('employee.results')"
                  :class="['flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14px] font-medium transition-all duration-200',
                           isActive('employee.results') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900']">
                <CheckBadgeIcon class="w-5 h-5" :class="isActive('employee.results') ? 'text-blue-600' : 'text-gray-400'" />
                Kết quả & Chứng chỉ
            </Link>
        </nav>

    </div>
</template>