<?php

namespace App\Services\SystemAdmin;

use App\Models\CourseClass;
use App\Models\Course;
use App\Models\Department;
use App\Models\Instructor;
use App\Models\User;
use App\Models\ClassEnrollment;
use App\Models\ClassSession;
use App\Models\Document;
use App\Models\Submission;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CourseClassService
{
    public function getFilteredClasses(array $filters)
    {
        $query = CourseClass::with(['course', 'instructor', 'department'])->latest();

        if (!empty($filters['tab']) && $filters['tab'] !== 'all') {
            $query->where('status', $filters['tab']);
        }
        if (!empty($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if (!empty($filters['keyword'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        return $query->paginate(15)->withQueryString();
    }

    public function getCreateData($courseId)
    {
        return [
            'courses' => Course::select('id', 'code', 'name', 'duration')
                ->whereIn('status', ['Chưa có lớp', 'Đang triển khai', 'Đang mở'])
                ->get(),
            'selectedCourse' => $courseId ? Course::find($courseId) : null,
            'departments' => Department::select('id', 'name')->orderBy('name')->get(),
            'instructors' => Instructor::select('id', 'name', 'email')->get()
        ];
    }

    public function createClass(array $data)
    {
        return DB::transaction(function () use ($data) {
            $lastClass = CourseClass::latest('id')->first();
            $nextId = $lastClass ? $lastClass->id + 1 : 1;
            $code = 'CLS-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            $status = $data['action'] === 'draft' ? 'Nháp' : 'Mở đăng ký';

            $courseClass = CourseClass::create([
                'course_id' => $data['course_id'],
                'code' => $code,
                'name' => $data['name'],
                'instructor_id' => $data['instructor_id'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'max_students' => $data['max_students'],
                'format' => $data['format'],
                'department_id' => $data['department_id'] ?? null,
                'status' => $status,
            ]);

            if (!empty($data['sessions']) && is_array($data['sessions'])) {
                foreach ($data['sessions'] as $index => $sessionData) {
                    ClassSession::create([
                        'course_class_id' => $courseClass->id,
                        'title' => 'Buổi học ' . ($index + 1),
                        'date' => $sessionData['date'],
                        'start_time' => $sessionData['start_time'],
                        'end_time' => $sessionData['end_time'],
                        'meet_link' => $sessionData['link'] ?? null,
                        'room' => $sessionData['room'] ?? null,
                    ]);
                }
            }

            $course = Course::find($data['course_id']);
            if ($course && $course->status === 'Chưa có lớp') {
                $course->update(['status' => 'Đang triển khai']);
            }

            return $courseClass;
        });
    }

    public function getClassDetails($id)
    {
        $courseClass = CourseClass::with([
            'course.assignments', 'instructor', 'department',
            'enrollments.user.department', 'documents' 
        ])->findOrFail($id);

        $submissions = Submission::where('course_class_id', $id)->get();
        $enrolledUserIds = $courseClass->enrollments->pluck('user_id')->toArray();

        $query = User::where('role', 3)->whereNotIn('id', $enrolledUserIds);
        if ($courseClass->department_id) {
            $query->where('department_id', $courseClass->department_id);
        }
        $availableUsers = $query->with('department')->get();

        $resultsData = $courseClass->enrollments->map(function ($enrol) use ($courseClass, $submissions) {
            $finalAssignment = $courseClass->course->assignments->where('type', 'final')->first();
            $subStatus = 'Chưa nộp';
            $score = '--';
            $subId = null;

            if ($finalAssignment) {
                $sub = $submissions->where('user_id', $enrol->user_id)->where('assignment_id', $finalAssignment->id)->first();
                if ($sub) {
                    $subStatus = $sub->status === 'graded' ? 'Đã chấm' : 'Chờ chấm';
                    $score = $sub->score ?? '--';
                    $subId = $sub->id;
                }
            }

            return [
                'user_id' => $enrol->user_id,
                'name' => $enrol->user->name,
                'emp_code' => 'NV-' . str_pad($enrol->user_id, 3, '0', STR_PAD_LEFT),
                'department' => $enrol->user->department->name ?? '--',
                'progress' => $enrol->progress_percent ?? 0,
                'class_status' => $enrol->status,
                'submission_status' => $subStatus,
                'score' => $score,
                'submission_id' => $subId,
                'has_final' => $finalAssignment ? true : false
            ];
        });

        return compact('courseClass', 'availableUsers', 'resultsData');
    }

    public function updateStatus(CourseClass $courseClass, $status)
    {
        $courseClass->update(['status' => $status]);

        if ($status === 'Đang học') {
            $students = $courseClass->enrollments()->with('user')->get()->pluck('user');
            foreach ($students as $student) {
                if ($student) {
                    $student->notify(new SystemNotification(
                        'Lớp học đã bắt đầu!',
                        "Lớp <strong>{$courseClass->name}</strong> đã chính thức diễn ra. Hãy vào học ngay!",
                        route('employee.my-classes.show', $courseClass->id)
                    ));
                }
            }
        }
    }

    public function addStudents(CourseClass $courseClass, array $userIds)
    {
        foreach ($userIds as $userId) {
            $enrollment = ClassEnrollment::firstOrCreate(
                ['course_class_id' => $courseClass->id, 'user_id' => $userId],
                ['status' => 'enrolled']
            );

            if ($enrollment->wasRecentlyCreated) {
                $student = User::find($userId);
                if ($student) {
                    $student->notify(new SystemNotification(
                        'Bạn có lớp học mới',
                        "Admin vừa xếp bạn vào Lớp: <strong>{$courseClass->name}</strong>. Hãy kiểm tra ngay.",
                        route('employee.my-classes.show', $courseClass->id)
                    ));
                }
            }
        }
    }

    public function removeStudent(CourseClass $courseClass, $studentId)
    {
        ClassEnrollment::where('course_class_id', $courseClass->id)->where('user_id', $studentId)->delete();
        
        $student = User::find($studentId);
        if ($student) {
            $student->notify(new SystemNotification(
                'Hủy ghi danh',
                "Bạn đã được rút khỏi Lớp: <strong>{$courseClass->name}</strong>.",
                route('employee.my-classes')
            ));
        }
    }

    public function uploadDocument(CourseClass $courseClass, array $data, $file = null)
    {
        $url = $data['url'] ?? '';
        if ($file) {
            $path = $file->store('documents', 's3');
            $url = Storage::disk('s3')->url($path);
        }

        return Document::create([
            'course_id' => $courseClass->course_id,
            'course_class_id' => $courseClass->id,
            'name' => $data['name'],
            'type' => $data['type'],
            'url' => $url,
        ]);
    }

    public function deleteDocument(Document $document)
    {
        if ($document->type !== 'link' && str_contains($document->url, 'supabase')) {
            $path = 'documents/' . basename($document->url);
            Storage::disk('s3')->delete($path);
        }
        $document->delete();
    }

    public function updateClass(CourseClass $courseClass, array $data)
    {
        DB::transaction(function () use ($courseClass, $data) {
            $courseClass->update([
                'course_id' => $data['course_id'],
                'name' => $data['name'],
                'instructor_id' => $data['instructor_id'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'department_id' => $data['department_id'] ?? null,
                'max_students' => $data['max_students'],
                'format' => $data['format'],
            ]);
            
            if ($data['action'] === 'published' && $courseClass->status === 'Nháp') {
                $courseClass->update(['status' => 'Mở đăng ký']);
            }

            if (!empty($data['deleted_session_ids'])) {
                ClassSession::whereIn('id', $data['deleted_session_ids'])->delete();
            }

            if (!empty($data['sessions']) && is_array($data['sessions'])) {
                foreach ($data['sessions'] as $index => $sessionData) {
                    $sessionId = $sessionData['id'] ?? null;
                    $updateData = [
                        'course_class_id' => $courseClass->id,
                        'title' => 'Buổi học ' . ($index + 1), 
                        'date' => $sessionData['date'],
                        'start_time' => $sessionData['start_time'],
                        'end_time' => $sessionData['end_time'],
                        'meet_link' => $sessionData['link'] ?? null, 
                        'room' => $sessionData['room'] ?? null,
                    ];

                    if ($sessionId && isset($sessionData['is_existing']) && $sessionData['is_existing']) {
                        ClassSession::where('id', $sessionId)->update($updateData);
                    } else {
                        ClassSession::create($updateData);
                    }
                }
            }
        });
    }
}