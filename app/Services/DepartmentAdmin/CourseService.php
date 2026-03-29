<?php

namespace App\Services\DepartmentAdmin;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\ClassEnrollment;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CourseService
{
    public function getFilteredCourses($departmentId, array $filters)
    {
        $query = Course::withCount('courseClasses')
            ->whereHas('departments', function ($q) use ($departmentId) {
                $q->where('departments.id', $departmentId);
            })->latest();

        if (!empty($filters['keyword'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        if (!empty($filters['format']) && $filters['format'] !== 'all') {
            $query->where('format', $filters['format']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->paginate(15)->withQueryString();
    }

    public function getCourseDetails($id)
    {
        $course = Course::with(['departments', 'courseClasses.instructor', 'documents'])->findOrFail($id);

        $courseInfo = [
            'code' => $course->code,
            'name' => $course->name,
            'target_audience' => $course->target_audience ?? 'Toàn công ty',
            'format' => $course->format,
            'scope' => $course->departments->pluck('name')->join(', ') ?: 'Không giới hạn',
            'duration' => $course->duration ? $course->duration . ' giờ' : '--',
        ];

        $classes = $course->courseClasses->map(function ($cls) {
            $capacity = ClassEnrollment::where('course_class_id', $cls->id)->count();
            return [
                'id' => $cls->id, // Phải có ID để gọi Modal
                'code' => $cls->code,
                'name' => $cls->name ?? 'Lớp ' . $cls->code,
                'instructor' => $cls->instructor ? $cls->instructor->name : 'Chưa phân công',
                'time' => $cls->start_date ? date('d/m/Y', strtotime($cls->start_date)) : 'Chưa xếp lịch',
                'capacity' => $capacity
            ];
        });

        $materials = $course->documents->map(function ($doc) {
            return [
                'name' => $doc->title,
                'url' => $doc->type === 'file' ? Storage::disk('s3')->url($doc->file_path) : $doc->url,
                'type' => $doc->type
            ];
        });

        return compact('courseInfo', 'classes', 'materials');
    }

    // ==============================================================
    // LOGIC LỌC NHÂN VIÊN THÔNG MINH DỰA TRÊN ĐỐI TƯỢNG ĐÀO TẠO
    // ==============================================================
    public function getEligibleEmployees($courseClassId, $departmentId)
    {
        $courseClass = CourseClass::with('course')->findOrFail($courseClassId);
        $targetAudience = $courseClass->course->target_audience;

        // 1. Lấy danh sách ID những người ĐÃ tham gia lớp này (Để loại bỏ)
        $enrolledUserIds = ClassEnrollment::where('course_class_id', $courseClassId)
                                          ->pluck('user_id');

        // 2. Query cơ sở: Những nhân sự thuộc phòng ban và chưa đăng ký
        $query = User::where('department_id', $departmentId)
                   ->where('role', \App\Enums\RoleEnum::EMPLOYEE->value)
                   ->whereNotIn('id', $enrolledUserIds);

        // 3. ÁP DỤNG BỘ LỌC THÔNG MINH
        if ($targetAudience === 'Cấp quản lý') {
            // Nếu khóa học cho Quản lý -> Chỉ show những người có is_manager = true
            $query->where('is_manager', true);
            
        } elseif ($targetAudience === 'Nhân viên mới') {
            // Nếu khóa học cho Lính mới -> Chỉ show những người join_date trong vòng 2 tháng
            $query->whereNotNull('join_date')
                  ->where('join_date', '>=', Carbon::now()->subMonths(2));
                  
        } elseif ($targetAudience === 'Cá nhân') {
            // Tự do chọn lọc, không ràng buộc thêm
        }

        return $query->select('id', 'name', 'email', 'job_title', 'is_manager')->get();
    }

    public function assignEmployeesToClass($courseClassId, array $employeeIds, $deadline, $assignerId)
    {
        $courseClass = CourseClass::with('course')->findOrFail($courseClassId);
        $deadlineText = $deadline ? 'Hạn chót: ' . date('d/m/Y', strtotime($deadline)) : 'Không có thời hạn cụ thể';

        foreach ($employeeIds as $employeeId) {
            // Ghi danh bắt buộc
            ClassEnrollment::create([
                'user_id' => $employeeId,
                'course_class_id' => $courseClassId,
                'status' => \App\Enums\EnrollmentStatusEnum::ENROLLED->value,
                'is_mandatory' => true,
                'deadline' => $deadline,
                'assigned_by' => $assignerId,
                'enrolled_at' => now(),
            ]);

            // Bắn Notification
            $employee = User::find($employeeId);
            if ($employee) {
                $employee->notify(new SystemNotification(
                    'Chỉ định đào tạo bắt buộc',
                    "Trưởng phòng vừa yêu cầu bạn tham gia lớp học <strong>{$courseClass->name}</strong> của khóa {$courseClass->course->name}. {$deadlineText}.",
                    route('employee.my-classes.show', $courseClassId)
                ));
            }
        }
    }
}