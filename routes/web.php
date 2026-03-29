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
use App\Http\Controllers\SystemAdmin\ReportController as SysReportController;
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

        // 👇 THÊM 2 ROUTE MỚI CHO TÍNH NĂNG CHỈ ĐỊNH NHÂN VIÊN
        Route::get('/classes/{courseClass}/eligible-employees', [DeptCourseController::class, 'getEligibleEmployees'])->name('classes.eligible-employees');
        Route::post('/classes/{courseClass}/assign-employees', [DeptCourseController::class, 'assignEmployees'])->name('classes.assign-employees');
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
        
        Route::get('/grades', [\App\Http\Controllers\SystemAdmin\GradeController::class, 'index'])->name('grades.index');
        Route::get('/grades/{id}', [\App\Http\Controllers\SystemAdmin\GradeController::class, 'show'])->name('grades.show');
        Route::put('/grades/{id}', [\App\Http\Controllers\SystemAdmin\GradeController::class, 'update'])->name('grades.update');
        Route::get('grades/export/template', [\App\Http\Controllers\SystemAdmin\GradeController::class, 'export'])->name('grades.export');
        Route::post('grades/import/excel', [\App\Http\Controllers\SystemAdmin\GradeController::class, 'import'])->name('grades.import');
        
        Route::get('/reports', [SysReportController::class, 'index'])->name('reports.index');
        Route::get('/employees', [SysEmployeeController::class, 'index'])->name('employees.index');
        Route::get('/courses/{course}/edit', [\App\Http\Controllers\SystemAdmin\CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [\App\Http\Controllers\SystemAdmin\CourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [\App\Http\Controllers\SystemAdmin\CourseController::class, 'destroy'])->name('courses.destroy');
        Route::get('/classes/{courseClass}/edit', [\App\Http\Controllers\SystemAdmin\CourseClassController::class, 'edit'])->name('classes.edit');
        Route::put('/classes/{courseClass}', [\App\Http\Controllers\SystemAdmin\CourseClassController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{courseClass}', [\App\Http\Controllers\SystemAdmin\CourseClassController::class, 'destroy'])->name('classes.destroy');
        Route::get('/classes/{id}/export-grades', [SysCourseClassController::class, 'exportGrades'])->name('classes.export-grades');
        Route::post('/classes/{id}/import-grades', [SysCourseClassController::class, 'importGrades'])->name('classes.import-grades');

        Route::put('/employees/{employee}/hr-info', [SysEmployeeController::class, 'updateHrInfo'])->name('employees.update-hr');
        Route::get('/classes/{courseClass}/eligible-employees', [SysCourseClassController::class, 'getEligibleEmployees'])->name('classes.eligible-employees');
Route::post('/classes/{courseClass}/assign-employees', [SysCourseClassController::class, 'assignEmployees'])->name('classes.assign-employees');
    });

    // ==============================================================
    // CỤM ROUTE DÀNH CHO NHÂN VIÊN (ROLE 3)
    // ==============================================================
    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/courses', [\App\Http\Controllers\Employee\CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{id}', [\App\Http\Controllers\Employee\CourseController::class, 'show'])->name('courses.show');
        Route::post('/classes/{id}/enroll', [\App\Http\Controllers\Employee\CourseController::class, 'enroll'])->name('classes.enroll');
        
        Route::get('/my-classes', [\App\Http\Controllers\Employee\MyClassController::class, 'index'])->name('my-classes');
        Route::get('/my-classes/{courseClass}', [\App\Http\Controllers\Employee\MyClassController::class, 'show'])->name('my-classes.show');
        Route::post('/my-classes/{courseClass}/complete-lesson', [\App\Http\Controllers\Employee\MyClassController::class, 'completeLesson'])->name('my-classes.complete-lesson');
        Route::post('/classes/{courseClass}/submissions', [\App\Http\Controllers\Employee\MyClassController::class, 'submitAssignment'])->name('submissions.store');
        Route::get('/my-schedule', [\App\Http\Controllers\Employee\MyScheduleController::class, 'index'])->name('my-schedule');
        Route::get('/results', [\App\Http\Controllers\Employee\ResultController::class, 'index'])->name('results');
        Route::get('/account', function () { return Inertia::render('Employee/Account/Index'); })->name('account');
    });
});

require __DIR__.'/auth.php';