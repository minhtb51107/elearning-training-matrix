<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentAdmin\TrainingRequestController;
use App\Http\Controllers\SystemAdmin\CourseController;
use App\Http\Controllers\SystemAdmin\CourseClassController;
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
        Route::get('/requests', [TrainingRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/create', [TrainingRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [TrainingRequestController::class, 'store'])->name('requests.store');
        Route::put('/requests/{trainingRequest}', [TrainingRequestController::class, 'update'])->name('requests.update');
        Route::get('/requests/{trainingRequest}', [TrainingRequestController::class, 'show'])->name('requests.show');
        
        Route::get('/courses', function () { return Inertia::render('DepartmentAdmin/Courses/Index'); })->name('courses.index');
        Route::get('/courses/{id}', function () { return Inertia::render('DepartmentAdmin/Courses/Show'); })->name('courses.show');
        Route::get('/employees', function () { return Inertia::render('DepartmentAdmin/Employees/Index'); })->name('employees.index');
    });

    // ==============================================================
    // CỤM ROUTE DÀNH CHO ADMIN HỆ THỐNG (ROLE 1)
    // ==============================================================
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/requests', [\App\Http\Controllers\SystemAdmin\TrainingRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{trainingRequest}', [\App\Http\Controllers\SystemAdmin\TrainingRequestController::class, 'show'])->name('requests.show');
        Route::put('/requests/{trainingRequest}/status', [\App\Http\Controllers\SystemAdmin\TrainingRequestController::class, 'updateStatus'])->name('requests.update-status');
        
        Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
        Route::get('/courses/{id}/statistics', function () { return Inertia::render('SystemAdmin/Courses/Statistics'); })->name('courses.statistics');
        
        // 👇 CỤM ROUTE LỚP HỌC (ĐÃ THÊM ROUTE HỌC VIÊN) 👇
        Route::get('/classes', [CourseClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/create', [CourseClassController::class, 'create'])->name('classes.create');
        Route::post('/classes', [CourseClassController::class, 'store'])->name('classes.store');
        Route::get('/classes/{courseClass}', [CourseClassController::class, 'show'])->name('classes.show');
        Route::put('/classes/{courseClass}/status', [CourseClassController::class, 'updateStatus'])->name('classes.update-status');
        Route::post('/classes/{courseClass}/students', [CourseClassController::class, 'addStudents'])->name('classes.add-students');
        Route::delete('/classes/{courseClass}/students/{studentId}', [CourseClassController::class, 'removeStudent'])->name('classes.remove-student');
        
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
        Route::get('/results', function () { return Inertia::render('Employee/Results/Index'); })->name('results');
        Route::get('/account', function () { return Inertia::render('Employee/Account/Index'); })->name('account');
    });
});

require __DIR__.'/auth.php';