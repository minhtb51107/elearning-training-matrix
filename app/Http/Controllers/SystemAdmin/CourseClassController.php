<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Department;
use App\Models\Instructor;
use App\Models\User;
use App\Models\ClassEnrollment;
use App\Models\ClassSession; 
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SystemNotification; // Bổ sung thư viện thông báo

class CourseClassController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseClass::with(['course', 'instructor', 'department'])->latest();

        if ($request->filled('tab') && $request->tab !== 'all') {
            $query->where('status', $request->tab);
        }
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('code', 'like', '%' . $request->keyword . '%');
            });
        }

        $classes = $query->paginate(15)->withQueryString();
        $courses = Course::select('id', 'name')->orderBy('name')->get();
        $departments = Department::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('SystemAdmin/Classes/Index', [
            'classes' => $classes,
            'courses' => $courses,
            'departments' => $departments,
            'filters' => $request->only(['tab', 'course_id', 'department_id', 'keyword'])
        ]);
    }

    public function create(Request $request)
    {
        $courseId = $request->query('course_id');
        $courses = Course::select('id', 'code', 'name', 'duration')
            ->whereIn('status', ['Chưa có lớp', 'Đang triển khai', 'Đang mở'])
            ->get();
        $selectedCourse = $courseId ? Course::find($courseId) : null;
        $departments = Department::select('id', 'name')->orderBy('name')->get();
        $instructors = Instructor::select('id', 'name', 'email')->get();

        return Inertia::render('SystemAdmin/Classes/Create', [
            'courses' => $courses,
            'selectedCourse' => $selectedCourse,
            'departments' => $departments,
            'instructors' => $instructors
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'instructor_id' => 'nullable|exists:instructors,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_students' => 'required|integer|min:1',
            'format' => 'required|string|in:Online,Offline,Hybrid',
            'department_id' => 'nullable|exists:departments,id',
            'action' => 'required|in:draft,published',
            'sessions' => 'nullable|array' 
        ]);

        $lastClass = CourseClass::latest('id')->first();
        $nextId = $lastClass ? $lastClass->id + 1 : 1;
        $code = 'CLS-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        
        $status = $validated['action'] === 'draft' ? 'Nháp' : 'Mở đăng ký';

        $courseClass = CourseClass::create([
            'course_id' => $validated['course_id'],
            'code' => $code,
            'name' => $validated['name'],
            'instructor_id' => $validated['instructor_id'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'max_students' => $validated['max_students'],
            'format' => $validated['format'],
            'department_id' => $validated['department_id'] ?? null,
            'status' => $status,
        ]);

        if ($request->has('sessions') && is_array($request->sessions)) {
            foreach ($request->sessions as $index => $sessionData) {
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

        $course = Course::find($validated['course_id']);
        if ($course && $course->status === 'Chưa có lớp') {
            $course->update(['status' => 'Đang triển khai']);
        }

        $message = $validated['action'] === 'draft' ? 'Đã lưu nháp lớp học!' : 'Đã tạo lớp học và mở đăng ký thành công!';
        return redirect()->route('system.classes.index')->with('success', $message);
    }

    public function show($id)
    {
        $courseClass = CourseClass::with([
            'course.assignments', 
            'instructor', 
            'department',
            'enrollments.user.department',
            'documents' 
        ])->findOrFail($id);

        $submissions = \App\Models\Submission::where('course_class_id', $id)->get();

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

        return Inertia::render('SystemAdmin/Classes/Show', [
            'courseClass' => $courseClass,
            'availableUsers' => $availableUsers,
            'resultsData' => $resultsData 
        ]);
    }

    public function updateStatus(Request $request, CourseClass $courseClass)
    {
        $validated = $request->validate(['status' => 'required|string|in:Nháp,Mở đăng ký,Đang học,Kết thúc']);
        $courseClass->update(['status' => $validated['status']]);

        // 👉 THÔNG BÁO CHO HỌC VIÊN KHI BẮT ĐẦU LỚP
        if ($validated['status'] === 'Đang học') {
            $students = $courseClass->enrollments()->with('user')->get()->pluck('user');
            foreach ($students as $student) {
                if ($student) {
                    $student->notify(new SystemNotification(
                        'Lớp học đã bắt đầu!',
                        'Lớp <strong>' . $courseClass->name . '</strong> đã chính thức diễn ra. Hãy vào học ngay!',
                        route('employee.my-classes.show', $courseClass->id)
                    ));
                }
            }
        }

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái lớp học thành: ' . $validated['status']);
    }

    public function addStudents(Request $request, CourseClass $courseClass)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        foreach ($validated['user_ids'] as $userId) {
            $enrollment = ClassEnrollment::firstOrCreate([
                'course_class_id' => $courseClass->id,
                'user_id' => $userId
            ], [
                'status' => 'enrolled'
            ]);

            // 👉 THÔNG BÁO CHO HỌC VIÊN ĐƯỢC THÊM
            if ($enrollment->wasRecentlyCreated) {
                $student = User::find($userId);
                if ($student) {
                    $student->notify(new SystemNotification(
                        'Bạn có lớp học mới',
                        'Admin vừa xếp bạn vào Lớp: <strong>' . $courseClass->name . '</strong>. Hãy kiểm tra ngay.',
                        route('employee.my-classes.show', $courseClass->id)
                    ));
                }
            }
        }

        return redirect()->back()->with('success', 'Đã thêm ' . count($validated['user_ids']) . ' học viên vào lớp!');
    }

    public function removeStudent(CourseClass $courseClass, $studentId)
    {
        ClassEnrollment::where('course_class_id', $courseClass->id)
            ->where('user_id', $studentId)
            ->delete();

        // 👉 THÔNG BÁO CHO HỌC VIÊN BỊ HỦY LỚP
        $student = User::find($studentId);
        if ($student) {
            $student->notify(new SystemNotification(
                'Hủy ghi danh',
                'Bạn đã được rút khỏi Lớp: <strong>' . $courseClass->name . '</strong>.',
                route('employee.my-classes')
            ));
        }

        return redirect()->back()->with('success', 'Đã gỡ học viên khỏi lớp!');
    }

    public function uploadDocument(Request $request, CourseClass $courseClass)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:pdf,link,pptx,doc,video',
            'file' => 'nullable|file|max:20480', 
            'url' => 'nullable|string'
        ]);

        $url = $validated['url'] ?? '';

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('documents', 's3');
            $url = Storage::disk('s3')->url($path);
        }

        Document::create([
            'course_id' => $courseClass->course_id,
            'course_class_id' => $courseClass->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'url' => $url,
        ]);

        return redirect()->back()->with('success', 'Đã tải tài liệu lên Đám mây Supabase thành công!');
    }

    public function deleteDocument(Document $document)
    {
        if ($document->type !== 'link' && str_contains($document->url, 'supabase')) {
            $path = 'documents/' . basename($document->url);
            Storage::disk('s3')->delete($path);
        }

        $document->delete();

        return redirect()->back()->with('success', 'Đã xóa tài liệu!');
    }

    public function edit(CourseClass $courseClass)
    {
        $courseClass->load('course', 'instructor', 'department', 'sessions');
        return Inertia::render('SystemAdmin/Classes/Edit', [
            'courseClass' => $courseClass,
            'courses' => \App\Models\Course::where('status', '!=', 'Đã kết thúc')->get(),
            'departments' => \App\Models\Department::all(),
            'instructors' => \App\Models\User::where('role', '!=', 3)->get()
        ]);
    }

    public function update(Request $request, CourseClass $courseClass)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'instructor_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:departments,id',
            'max_students' => 'required|integer|min:1',
            'format' => 'required|string',
        ]);

        $courseClass->update($validated);
        
        if ($request->action === 'published' && $courseClass->status === 'Nháp') {
            $courseClass->update(['status' => 'Mở đăng ký']);
        }

        if ($request->has('deleted_session_ids')) {
            ClassSession::whereIn('id', $request->deleted_session_ids)->delete();
        }

        if ($request->has('sessions') && is_array($request->sessions)) {
            foreach ($request->sessions as $index => $sessionData) {
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

        return redirect()->route('system.classes.index')->with('success', 'Đã cập nhật lớp học và lịch học thành công!');
    }

    public function destroy(CourseClass $courseClass)
    {
        $courseClass->delete();
        return redirect()->route('system.classes.index')->with('success', 'Đã xóa lớp học!');
    }
}