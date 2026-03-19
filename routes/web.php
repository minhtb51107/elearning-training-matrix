<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
<<<<<<< Updated upstream
=======
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseClassController;
>>>>>>> Stashed changes

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

<<<<<<< Updated upstream
=======
Route::middleware(['auth', 'system.admin'])->prefix('admin')->name('admin.')->group(function () {
    // [Đã có] Danh sách
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    
    // [THÊM MỚI] Hiển thị form tạo
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    // [THÊM MỚI] Xử lý lưu dữ liệu
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');

    // Cập nhật Khóa học
    Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    // Xóa Khóa học
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

    Route::get('/courses/{course}/classes', [CourseClassController::class, 'index'])->name('courses.classes.index');
    Route::post('/courses/{course}/classes', [CourseClassController::class, 'store'])->name('courses.classes.store');
    Route::put('/classes/{courseClass}', [CourseClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{courseClass}', [CourseClassController::class, 'destroy'])->name('classes.destroy');
});

>>>>>>> Stashed changes
require __DIR__.'/auth.php';
