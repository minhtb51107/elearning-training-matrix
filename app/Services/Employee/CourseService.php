<?php

namespace App\Services\Employee;

use App\Models\Course;
use App\Models\User;
use App\Models\CourseClass;
use App\Models\ClassEnrollment;
use App\Enums\EnrollmentStatusEnum;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CourseService
{
    public function getAvailableCourses($user, array $filters)
    {
        $query = Course::where(function($q) use ($user) {
            
            // 1. Luôn thấy các khóa học dành cho Toàn công ty
            $q->where('target_audience', 'Toàn công ty')
            
            // 2. Hoặc các khóa học được phân bổ CỤ THỂ cho phòng ban của họ
              ->orWhereHas('departments', function($subQ) use ($user) {
                  $subQ->where('departments.id', $user->department_id);
              });

            // 3. NẾU LÀ QUẢN LÝ: Thấy thêm các khóa học dành riêng cho Cấp quản lý
            if ($user->is_manager) {
                $q->orWhere('target_audience', 'Cấp quản lý');
            }

            // 4. NẾU LÀ LÍNH MỚI: Thấy thêm khóa Nhân viên mới
            if ($user->join_date && Carbon::parse($user->join_date)->diffInMonths(now()) <= 2) {
                $q->orWhere('target_audience', 'Nhân viên mới');
            }

        })->latest();

        if (!empty($filters['keyword'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        if (!empty($filters['type']) && $filters['type'] !== 'Tất cả') {
            if ($filters['type'] === 'Bắt buộc') {
                $query->whereIn('target_audience', ['Toàn phòng', 'Toàn công ty']);
            } else {
                $query->whereNotIn('target_audience', ['Toàn phòng', 'Toàn công ty']);
            }
        }

        return $query->paginate(6)->withQueryString();
    }

    public function getCourseDetails($id, $userId)
    {
        $course = Course::with([
            'courseClasses' => function($query) {
                $query->where('status', '!=', 'completed')->with('instructor');
            },
            'lessons',
            'documents'
        ])->findOrFail($id);

        $user = User::find($userId);

        // KHẮC PHỤC LỖ HỔNG BẢO MẬT (IDOR): Kiểm tra quyền xem chi tiết khóa học
        $isAllowed = $course->target_audience === 'Toàn công ty' 
            || ($course->target_audience === 'Cấp quản lý' && $user->is_manager)
            || ($course->target_audience === 'Nhân viên mới' && $user->join_date && Carbon::parse($user->join_date)->diffInMonths(now()) <= 2)
            || $course->departments()->where('departments.id', $user->department_id)->exists();

        if (!$isAllowed) {
            abort(403, 'Bạn không có quyền truy cập khóa học này.');
        }

        $classIds = $course->courseClasses->pluck('id');
        
        $enrollmentCounts = ClassEnrollment::whereIn('course_class_id', $classIds)
            ->selectRaw('course_class_id, count(*) as count')
            ->groupBy('course_class_id')
            ->pluck('count', 'course_class_id');

        $userEnrolledClassIds = ClassEnrollment::whereIn('course_class_id', $classIds)
            ->where('user_id', $userId)
            ->pluck('course_class_id')
            ->toArray();

        $classes = $course->courseClasses->map(function ($cls) use ($enrollmentCounts, $userEnrolledClassIds) {
            $enrolledCount = $enrollmentCounts[$cls->id] ?? 0;
            $maxStudents = $cls->max_students ?? 30; 
            $isFull = $enrolledCount >= $maxStudents;

            $isEnrolled = in_array($cls->id, $userEnrolledClassIds);

            return [
                'id' => $cls->id,
                'name' => $cls->code,
                'time' => ($cls->start_date ? date('d/m/Y', strtotime($cls->start_date)) : 'Đang cập nhật'),
                'instructor' => $cls->instructor ? $cls->instructor->name : 'Chưa phân công',
                'slot' => $isFull ? 'ĐÃ ĐỦ SỐ LƯỢNG' : ($enrolledCount . '/' . $maxStudents),
                'isFull' => $isFull,
                'isEnrolled' => $isEnrolled
            ];
        });

        return [
            'id' => $course->id,
            'name' => $course->name,
            'overview' => array_filter([
                '- Mục tiêu: ' . ($course->description ?: 'Đang cập nhật'),
                '- Đối tượng: ' . $course->target_audience,
                '- Hình thức: ' . $course->format,
                '- Thời lượng: ' . $course->duration . 'h'
            ]),
            'content' => $course->lessons->map(function($lesson) {
                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'type' => $lesson->media_type, 
                    'time' => $lesson->duration > 0 ? $lesson->duration . ' phút' : '--',
                    'url' => $lesson->media_type === 'youtube' 
                        ? $lesson->media_url 
                        : ($lesson->media_url ? Storage::disk('s3')->url($lesson->media_url) : '#')
                ];
            }),
            'documents' => $course->documents->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'url' => $doc->type === 'link' 
                        ? $doc->url 
                        : ($doc->file_path ? Storage::disk('s3')->url($doc->file_path) : '#')
                ];
            }),
            'classes' => $classes,
        ];
    }

    public function enrollUser($classId, $user)
    {
        $courseClass = CourseClass::find($classId);
        
        if (!$courseClass) {
            return false;
        }

        $enrolledCount = ClassEnrollment::where('course_class_id', $classId)->count();
        $maxStudents = $courseClass->max_students ?? 30;

        if ($enrolledCount >= $maxStudents) {
            return false; 
        }

        $exists = ClassEnrollment::where('course_class_id', $classId)
                    ->where('user_id', $user->id)
                    ->exists();

        if (!$exists) {
            ClassEnrollment::create([
                'course_class_id' => $classId,
                'user_id' => $user->id,
                'status' => EnrollmentStatusEnum::ENROLLED->value
            ]);

            $user->notify(new SystemNotification(
                'Đăng ký thành công',
                'Bạn đã ghi danh thành công vào lớp: <strong>' . ($courseClass->name ?? 'N/A') . '</strong>. Hãy chuẩn bị lịch học nhé!',
                route('employee.my-classes.show', $classId)
            ));

            return true;
        }

        return false;
    }
}