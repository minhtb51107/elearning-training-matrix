<?php

namespace App\Services\DepartmentAdmin;

use App\Models\Course;
use App\Models\ClassEnrollment;
use Illuminate\Support\Facades\Storage;

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
}