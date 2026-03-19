<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentAdmin\TrainingRequestController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // Nếu đã đăng nhập thì vô Dashboard, chưa thì ra Login
    return redirect()->route('login'); 
});

// Trang Dashboard tạm thời của hệ thống
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Cụm Route cấu hình tài khoản cá nhân
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==============================================================
// CỤM ROUTE DÀNH CHO ADMIN PHÒNG BAN (QUẢN LÝ YÊU CẦU ĐÀO TẠO)
// ==============================================================
// ==============================================================
// CỤM ROUTE DÀNH CHO ADMIN PHÒNG BAN
// ==============================================================
Route::middleware(['auth'])->prefix('department')->name('department.')->group(function () {
    
    // (Các route requests cũ giữ nguyên ở đây...)
    Route::get('/requests', [TrainingRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [TrainingRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [TrainingRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{trainingRequest}', [TrainingRequestController::class, 'show'])->name('requests.show');
    Route::put('/requests/{trainingRequest}', [TrainingRequestController::class, 'update'])->name('requests.update');

    // 👇 THÊM 2 ROUTE NÀY VÀO ĐỂ GỌI GIAO DIỆN (UI-First) 👇
    Route::get('/courses', function () {
        return Inertia::render('DepartmentAdmin/Courses/Index');
    })->name('courses.index');

    Route::get('/courses/{id}', function () {
        return inertia('DepartmentAdmin/Courses/Show');
    })->name('courses.show');

    Route::get('/employees', function () {
        return Inertia::render('DepartmentAdmin/Employees/Index');
    })->name('employees.index');
});

require __DIR__.'/auth.php';