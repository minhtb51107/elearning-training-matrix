<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Tài khoản cá nhân" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 border border-gray-200">
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-8 border-b pb-4">Tài khoản</h2>

                    <div class="mb-10">
                        <h3 class="text-[17px] font-bold text-gray-800 mb-4">Thông tin cá nhân</h3>
                        <div class="border border-gray-300 rounded-sm p-8 flex items-center gap-12 bg-white shadow-sm">
                            
                            <div class="w-32 h-32 rounded-full border border-gray-400 bg-gray-50 flex-shrink-0"></div>
                            
                            <div class="grid grid-cols-[120px_1fr] gap-y-2 text-[15px] text-gray-800">
                                <span class="font-bold">Họ và tên:</span> <span>Nguyễn Văn A</span>
                                <span class="font-bold">Mã nhân viên:</span> <span>NV-02341</span>
                                <span class="font-bold">Email:</span> <span>nguyenvana@company.com</span>
                                <span class="font-bold">Phòng ban:</span> <span>Kinh doanh</span>
                                <span class="font-bold">Chức vụ:</span> <span>Nhân viên kinh doanh</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-[17px] font-bold text-gray-800 mb-4">Phiên & Thiết bị</h3>
                        <div class="pl-6 border-l-2 border-transparent">
                            
                            <div class="mb-6">
                                <h4 class="font-bold text-[15px] text-gray-800 mb-2">Thiết bị hiện tại:</h4>
                                <p class="text-[14px] text-gray-700 mb-1">Chrome / Windows 11</p>
                                <p class="text-[13px] text-gray-600 mb-1">Đăng nhập lúc: 19/01/2026 – 07:45</p>
                                <p class="text-[13px] text-gray-600">Vị trí: TP. Hồ Chí Minh</p>
                            </div>

                            <div class="border-t border-gray-200 pt-6 mb-8">
                                <h4 class="font-bold text-[15px] text-gray-800 mb-2">Thiết bị khác:</h4>
                                <p class="text-[14px] text-gray-700 mb-1">Safari / iPhone</p>
                                <p class="text-[13px] text-gray-600">Đăng nhập lúc: 18/01/2026 – 21:10</p>
                            </div>

                            <button class="text-[#c93b42] hover:text-red-800 font-bold text-[15px] transition focus:outline-none">
                                [ Đăng xuất khỏi tất cả thiết bị ]
                            </button>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>