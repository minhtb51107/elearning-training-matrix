<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentAdmin\TrainingRequestController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==============================================================
    // CỤM ROUTE DÀNH CHO ADMIN PHÒNG BAN (ROLE 2)
    // ==============================================================
    // ==============================================================
    // CỤM ROUTE DÀNH CHO ADMIN PHÒNG BAN (ROLE 2)
    // ==============================================================
    Route::prefix('department')->name('department.')->group(function () {
        Route::get('/requests', [TrainingRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/create', [TrainingRequestController::class, 'create'])->name('requests.create');
        
        // 👇 THÊM 2 ROUTE NÀY ĐỂ XỬ LÝ LƯU VÀ CẬP NHẬT FORM 👇
        Route::post('/requests', [TrainingRequestController::class, 'store'])->name('requests.store');
        Route::put('/requests/{trainingRequest}', [TrainingRequestController::class, 'update'])->name('requests.update');
        
        // 👇 SỬA LẠI {id} THÀNH {trainingRequest} ĐỂ MATCH VỚI CONTROLLER 👇
        Route::get('/requests/{trainingRequest}', [TrainingRequestController::class, 'show'])->name('requests.show');
        
        Route::get('/courses', function () { return Inertia::render('DepartmentAdmin/Courses/Index'); })->name('courses.index');
        Route::get('/courses/{id}', function () { return Inertia::render('DepartmentAdmin/Courses/Show'); })->name('courses.show');
        Route::get('/employees', function () { return Inertia::render('DepartmentAdmin/Employees/Index'); })->name('employees.index');
    });

    // ==============================================================
    // CỤM ROUTE DÀNH CHO ADMIN HỆ THỐNG (ROLE 1)
    // ==============================================================
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/requests', function () { return Inertia::render('SystemAdmin/Requests/Index'); })->name('requests.index');
        Route::get('/requests/{id}', function () { return Inertia::render('SystemAdmin/Requests/Show'); })->name('requests.show');
        Route::get('/courses', function () { return Inertia::render('SystemAdmin/Courses/Index'); })->name('courses.index');
        Route::get('/courses/create', function () { return Inertia::render('SystemAdmin/Courses/Create'); })->name('courses.create');
        Route::get('/courses/{id}/statistics', function () { return Inertia::render('SystemAdmin/Courses/Statistics'); })->name('courses.statistics');
        Route::get('/courses/{id}', function () { return Inertia::render('SystemAdmin/Courses/Show'); })->name('courses.show');
        Route::get('/classes', function () { return Inertia::render('SystemAdmin/Classes/Index'); })->name('classes.index');
        Route::get('/classes/create', function () { return Inertia::render('SystemAdmin/Classes/Create'); })->name('classes.create');
        Route::get('/classes/{id}', function () { return Inertia::render('SystemAdmin/Classes/Show'); })->name('classes.show');
        Route::get('/grades', function () { return Inertia::render('SystemAdmin/Grades/Index'); })->name('grades.index');
        Route::get('/grades/{id}', function () { return Inertia::render('SystemAdmin/Grades/Show'); })->name('grades.show');
        Route::get('/reports', function () { return Inertia::render('SystemAdmin/Reports/Index'); })->name('reports.index');
        Route::get('/employees', function () { return Inertia::render('SystemAdmin/Employees/Index'); })->name('employees.index');
    });

    // ==============================================================
    // CỤM ROUTE DÀNH CHO NHÂN VIÊN (ROLE 3)
    // ==============================================================
    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/courses', function () { return Inertia::render('Employee/Courses/Index'); })->name('courses.index');
        Route::get('/courses/{id}', function () { return Inertia::render('Employee/Courses/Show'); })->name('courses.show');
        Route::get('/my-classes', function () { return Inertia::render('Employee/MyClasses/Index'); })->name('my-classes');
        Route::get('/my-schedule', function () { return Inertia::render('Employee/MySchedule/Index'); })->name('my-schedule');
        
        // 👇 THÊM 2 ROUTE NÀY CHO KẾT QUẢ VÀ TÀI KHOẢN 👇
        Route::get('/results', function () { return Inertia::render('Employee/Results/Index'); })->name('results');
        Route::get('/account', function () { return Inertia::render('Employee/Account/Index'); })->name('account');
    });
});

require __DIR__.'/auth.php';