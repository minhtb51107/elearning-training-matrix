<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import SystemAdminDashboard from '@/Pages/Partials/SystemAdminDashboard.vue';
import DepartmentAdminDashboard from '@/Pages/Partials/DepartmentAdminDashboard.vue';
import EmployeeDashboard from '@/Pages/Partials/EmployeeDashboard.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const dashboardData = computed(() => page.props.dashboardData);
// Hứng bộ lọc từ Controller
const filters = computed(() => page.props.filters); 
</script>

<template>
    <Head title="Trang chủ Dashboard" />

    <AuthenticatedLayout>
        <SystemAdminDashboard v-if="user.role === 1" />
        
        <DepartmentAdminDashboard v-else-if="user.role === 2" :dashboardData="dashboardData" :filters="filters" />
        
        <EmployeeDashboard v-else-if="user.role === 3" :user="user" :dashboardData="dashboardData" />
        
        <div v-else class="py-12 text-center text-gray-500">
            <p>Trang chủ cho Vai trò này đang được xây dựng...</p>
        </div>
    </AuthenticatedLayout>
</template>