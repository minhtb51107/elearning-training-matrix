<?php

namespace App\Services\Employee;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\ClassEnrollment;
use App\Enums\EnrollmentStatusEnum;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Storage;

class CourseService
{
    public function getAvailableCourses($user, array $filters)
    {
        $query = Course::whereHas('departments', function($q) use ($user) {
            $q->where('departments.id', $user->department_id);
        })->latest();

        if (!empty($filters['keyword'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        if (!empty($filters['type']) && $filters['type'] !== 'Tất cả') {
            if ($filters['type'] === 'Bắt buộc') {
                $query->where('target_audience', 'Toàn phòng');
            } else {
                $query->where('target_audience', '!=', 'Toàn phòng');
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

        $classes = $course->courseClasses->map(function ($cls) use ($userId) {
            $enrolledCount = ClassEnrollment::where('course_class_id', $cls->id)->count();
            $maxStudents = $cls->max_students ?? 30; 
            $isFull = $enrolledCount >= $maxStudents;

            $isEnrolled = ClassEnrollment::where('course_class_id', $cls->id)
                            ->where('user_id', $userId)
                            ->exists();

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
        $exists = ClassEnrollment::where('course_class_id', $classId)
                    ->where('user_id', $user->id)
                    ->exists();

        if (!$exists) {
            ClassEnrollment::create([
                'course_class_id' => $classId,
                'user_id' => $user->id,
                'status' => EnrollmentStatusEnum::ENROLLED->value
            ]);

            $courseClass = CourseClass::find($classId);
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