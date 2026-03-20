<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentAdmin\TrainingRequestController as DeptTrainingRequestController;
use App\Http\Controllers\DepartmentAdmin\CourseController as DeptCourseController;
use App\Http\Controllers\DepartmentAdmin\EmployeeController as DeptEmployeeController;
use App\Http\Controllers\SystemAdmin\CourseController as SysCourseController;
use App\Http\Controllers\SystemAdmin\CourseClassController as SysCourseClassController;
use App\Http\Controllers\SystemAdmin\TrainingRequestController as SysTrainingRequestController;
use App\Http\Controllers\SystemAdmin\EmployeeController as SysEmployeeController;
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
    Route::prefix('department')->name('department.')->group(function () {
        Route::get('/requests', [DeptTrainingRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/create', [DeptTrainingRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [DeptTrainingRequestController::class, 'store'])->name('requests.store');
        Route::put('/requests/{trainingRequest}', [DeptTrainingRequestController::class, 'update'])->name('requests.update');
        Route::get('/requests/{trainingRequest}', [DeptTrainingRequestController::class, 'show'])->name('requests.show');
        
        // Đã cập nhật Route thật cho Courses và Employees
        Route::get('/courses', [DeptCourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{id}', function () { return Inertia::render('DepartmentAdmin/Courses/Show'); })->name('courses.show');
        Route::get('/employees', [DeptEmployeeController::class, 'index'])->name('employees.index');
    });

    // ==============================================================
    // CỤM ROUTE DÀNH CHO ADMIN HỆ THỐNG (ROLE 1)
    // ==============================================================
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/requests', [SysTrainingRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{trainingRequest}', [SysTrainingRequestController::class, 'show'])->name('requests.show');
        Route::put('/requests/{trainingRequest}/status', [SysTrainingRequestController::class, 'updateStatus'])->name('requests.update-status');
        
        Route::get('/courses', [SysCourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/create', [SysCourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [SysCourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}', [SysCourseController::class, 'show'])->name('courses.show');
        Route::get('/courses/{id}/statistics', function () { return Inertia::render('SystemAdmin/Courses/Statistics'); })->name('courses.statistics');
        
        Route::get('/classes', [SysCourseClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/create', [SysCourseClassController::class, 'create'])->name('classes.create');
        Route::post('/classes', [SysCourseClassController::class, 'store'])->name('classes.store');
        Route::get('/classes/{courseClass}', [SysCourseClassController::class, 'show'])->name('classes.show');
        Route::put('/classes/{courseClass}/status', [SysCourseClassController::class, 'updateStatus'])->name('classes.update-status');
        Route::post('/classes/{courseClass}/students', [SysCourseClassController::class, 'addStudents'])->name('classes.add-students');
        Route::delete('/classes/{courseClass}/students/{studentId}', [SysCourseClassController::class, 'removeStudent'])->name('classes.remove-student');
        Route::post('/classes/{courseClass}/documents', [SysCourseClassController::class, 'uploadDocument'])->name('classes.upload-document');
        Route::delete('/classes/documents/{document}', [SysCourseClassController::class, 'deleteDocument'])->name('classes.delete-document');
        
        Route::get('/grades', function () { return Inertia::render('SystemAdmin/Grades/Index'); })->name('grades.index');
        Route::get('/grades/{id}', function () { return Inertia::render('SystemAdmin/Grades/Show'); })->name('grades.show');
        Route::get('/reports', function () { return Inertia::render('SystemAdmin/Reports/Index'); })->name('reports.index');
        
        // 👇 Đã cập nhật Route thật cho Employee System Admin 👇
        Route::get('/employees', [SysEmployeeController::class, 'index'])->name('employees.index');
    });

    // ==============================================================
    // CỤM ROUTE DÀNH CHO NHÂN VIÊN (ROLE 3)
    // ==============================================================
    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/courses', function () { return Inertia::render('Employee/Courses/Index'); })->name('courses.index');
        Route::get('/courses/{id}', function () { return Inertia::render('Employee/Courses/Show'); })->name('courses.show');
        Route::get('/my-classes', function () { return Inertia::render('Employee/MyClasses/Index'); })->name('my-classes');
        Route::get('/my-schedule', function () { return Inertia::render('Employee/MySchedule/Index'); })->name('my-schedule');
        Route::get('/results', function () { return Inertia::render('Employee/Results/Index'); })->name('results');
        Route::get('/account', function () { return Inertia::render('Employee/Account/Index'); })->name('account');
    });
});

require __DIR__.'/auth.php';