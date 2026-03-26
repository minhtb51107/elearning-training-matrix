<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController; 
use App\Http\Controllers\DepartmentAdmin\TrainingRequestController as DeptTrainingRequestController;
use App\Http\Controllers\DepartmentAdmin\CourseController as DeptCourseController;
use App\Http\Controllers\DepartmentAdmin\EmployeeController as DeptEmployeeController;
use App\Http\Controllers\SystemAdmin\CourseController as SysCourseController;
use App\Http\Controllers\SystemAdmin\CourseClassController as SysCourseClassController;
use App\Http\Controllers\SystemAdmin\TrainingRequestController as SysTrainingRequestController;
use App\Http\Controllers\SystemAdmin\EmployeeController as SysEmployeeController;
use App\Http\Controllers\SystemAdmin\ReportController as SysReportController; // Thêm dòng này
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

// Gọi vào DashboardController để lấy dữ liệu thống kê
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // ==============================================================
    // CỤM ROUTE DÀNH CHO ADMIN PHÒNG BAN (ROLE 2)
    // ==============================================================
    Route::prefix('department')->name('department.')->group(function () {
        Route::get('/requests', [DeptTrainingRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/create', [DeptTrainingRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [DeptTrainingRequestController::class, 'store'])->name('requests.store');
        Route::put('/requests/{trainingRequest}', [DeptTrainingRequestController::class, 'update'])->name('requests.update');
        Route::get('/requests/{trainingRequest}', [DeptTrainingRequestController::class, 'show'])->name('requests.show');
        
        Route::get('/courses', [DeptCourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{id}', [DeptCourseController::class, 'show'])->name('courses.show');
        Route::get('/employees', [DeptEmployeeController::class, 'index'])->name('employees.index');
    });

    // ==============================================================
    // CỤM ROUTE DÀNH CHO ADMIN HỆ THỐNG (ROLE 1)
    // ==============================================================
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/requests', [SysTrainingRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{trainingRequest}', [SysTrainingRequestController::class, 'show'])->name('requests.show');
        Route::put('/requests/{trainingRequest}/status', [SysTrainingRequestController::class, 'updateStatus'])->name('requests.update-status');
        Route::post('/requests/bulk-approve', [SysTrainingRequestController::class, 'bulkApprove'])->name('requests.bulk-approve');
        
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
        
        // ĐÁNH GIÁ & CHẤM BÀI
        Route::get('/grades', [\App\Http\Controllers\SystemAdmin\GradeController::class, 'index'])->name('grades.index');
        Route::get('/grades/{id}', [\App\Http\Controllers\SystemAdmin\GradeController::class, 'show'])->name('grades.show');
        Route::put('/grades/{id}', [\App\Http\Controllers\SystemAdmin\GradeController::class, 'update'])->name('grades.update');
        
        // 👇 ĐÃ SỬA: Nối dây điện cho trang Reports
        Route::get('/reports', [SysReportController::class, 'index'])->name('reports.index');
        
        Route::get('/employees', [SysEmployeeController::class, 'index'])->name('employees.index');
        
        // GIAO DIỆN SỬA KHÓA HỌC
        Route::get('/courses/{course}/edit', [\App\Http\Controllers\SystemAdmin\CourseController::class, 'edit'])->name('courses.edit');

        // LƯU CẬP NHẬT KHÓA HỌC
        Route::put('/courses/{course}', [\App\Http\Controllers\SystemAdmin\CourseController::class, 'update'])->name('courses.update');

        // XÓA KHÓA HỌC
        Route::delete('/courses/{course}', [\App\Http\Controllers\SystemAdmin\CourseController::class, 'destroy'])->name('courses.destroy');

        Route::get('/classes/{courseClass}/edit', [\App\Http\Controllers\SystemAdmin\CourseClassController::class, 'edit'])->name('classes.edit');
        Route::put('/classes/{courseClass}', [\App\Http\Controllers\SystemAdmin\CourseClassController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{courseClass}', [\App\Http\Controllers\SystemAdmin\CourseClassController::class, 'destroy'])->name('classes.destroy');
    });

    // ==============================================================
    // CỤM ROUTE DÀNH CHO NHÂN VIÊN (ROLE 3)
    // ==============================================================
    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/courses', [\App\Http\Controllers\Employee\CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{id}', [\App\Http\Controllers\Employee\CourseController::class, 'show'])->name('courses.show');
        Route::post('/classes/{id}/enroll', [\App\Http\Controllers\Employee\CourseController::class, 'enroll'])->name('classes.enroll');
        
        // Danh sách Lớp học của tôi
        Route::get('/my-classes', [\App\Http\Controllers\Employee\MyClassController::class, 'index'])->name('my-classes');
        
        // Chi tiết Lớp học của tôi (Phòng học ảo)
        Route::get('/my-classes/{courseClass}', [\App\Http\Controllers\Employee\MyClassController::class, 'show'])->name('my-classes.show');

        // Route API để đánh dấu hoàn thành bài giảng (Video/Slide)
        Route::post('/my-classes/{courseClass}/complete-lesson', [\App\Http\Controllers\Employee\MyClassController::class, 'completeLesson'])->name('my-classes.complete-lesson');
        
        // Route để nộp bài tập / đánh giá
        Route::post('/classes/{courseClass}/submissions', [\App\Http\Controllers\Employee\MyClassController::class, 'submitAssignment'])->name('submissions.store');

        Route::get('/my-schedule', [\App\Http\Controllers\Employee\MyScheduleController::class, 'index'])->name('my-schedule');
        Route::get('/results', [\App\Http\Controllers\Employee\ResultController::class, 'index'])->name('results');
        Route::get('/account', function () { return Inertia::render('Employee/Account/Index'); })->name('account');
    });
});

require __DIR__.'/auth.php';