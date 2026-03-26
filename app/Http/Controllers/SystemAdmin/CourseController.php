<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\TrainingRequest;
use App\Models\Department; 
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SystemNotification; // Bổ sung thư viện thông báo

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::latest();

        if ($request->filled('tab') && $request->tab !== 'all') {
            $query->where('status', $request->tab);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('scope')) {
            $query->where('target_audience', $request->scope);
        }
        if ($request->filled('source')) {
            if ($request->source === 'Yêu cầu') {
                $query->whereNull('reason'); 
            } elseif ($request->source === 'Nội bộ') {
                $query->whereNotNull('reason'); 
            }
        }
        if ($request->filled('status')) {
            if ($request->status === 'Chưa có lớp') {
                $query->where('status', 'Chưa có lớp');
            } elseif ($request->status === 'Có lớp') {
                $query->where('status', '!=', 'Chưa có lớp');
            }
        }
        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('code', 'like', '%' . $request->keyword . '%');
            });
        }

        $courses = $query->paginate(15)->withQueryString();
        $departments = Department::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('SystemAdmin/Courses/Index', [
            'courses' => $courses,
            'departments' => $departments,
            'filters' => $request->only(['tab', 'date', 'scope', 'source', 'status', 'keyword'])
        ]);
    }

    public function create(Request $request)
    {
        $approvedRequests = TrainingRequest::with('department')
            ->where('status', 'approved')
            ->latest()
            ->get();

        return Inertia::render('SystemAdmin/Courses/Create', [
            'approvedRequests' => $approvedRequests,
            'preselectedRequestId' => $request->query('request_id') 
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_audience' => 'required|string',
            'duration' => 'required|numeric',
            'format' => 'required|string',
            'instructor' => 'nullable|string',
            'notes' => 'nullable|string',
            'description' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $lastCourse = Course::latest('id')->first();
        $nextId = $lastCourse ? $lastCourse->id + 1 : 1;
        $validated['code'] = 'KH-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        $validated['status'] = 'Chưa có lớp';

        $course = Course::create($validated);

        if ($request->has('request_ids') && count($request->request_ids) > 0) {
            $requests = TrainingRequest::whereIn('id', $request->request_ids)->get();
            
            TrainingRequest::whereIn('id', $request->request_ids)->update([
                'status' => 'processed',
                'course_id' => $course->id,
                'updated_at' => now()
            ]);
            
            $departmentIds = $requests->pluck('department_id')->toArray();
            $course->departments()->sync($departmentIds);

            // 👉 BẮN THÔNG BÁO CHO CÁC TRƯỞNG PHÒNG ĐÃ GỬI YÊU CẦU NÀY
            foreach ($requests as $req) {
                $requester = User::find($req->requester_id);
                if ($requester) {
                    $requester->notify(new SystemNotification(
                        'Khóa học đã được triển khai',
                        'Yêu cầu <strong>' . $req->course_name . '</strong> của bạn đã được Admin tạo thành khóa học chính thức.',
                        route('department.courses.show', $course->id)
                    ));
                }
            }
        }

        // LƯU BÀI GIẢNG (LESSONS)
        if ($request->has('lessons') && is_array($request->lessons)) {
            foreach ($request->lessons as $index => $lessonData) {
                $mediaUrl = $lessonData['media_url'] ?? null;
                if (in_array($lessonData['media_type'], ['video_upload', 'slide_pdf']) && isset($lessonData['file'])) {
                    $mediaUrl = $lessonData['file']->store('course_lessons', 's3');
                }
                $course->lessons()->create([
                    'title' => $lessonData['title'],
                    'media_type' => $lessonData['media_type'],
                    'media_url' => $mediaUrl,
                    'duration' => $lessonData['duration'] ?? 0,
                    'order_num' => $index + 1,
                ]);
            }
        }

        // LƯU BÀI TẬP / ĐỀ BÀI (ASSIGNMENTS)
        if ($request->has('assignments') && is_array($request->assignments)) {
            foreach ($request->assignments as $assignmentData) {
                $questionsJson = isset($assignmentData['questions']) && is_array($assignmentData['questions']) 
                    ? json_encode($assignmentData['questions'], JSON_UNESCAPED_UNICODE) 
                    : json_encode([$assignmentData['content'] ?? ''], JSON_UNESCAPED_UNICODE);

                $course->assignments()->create([
                    'title' => $assignmentData['title'],
                    'type' => $assignmentData['type'],
                    'content' => $questionsJson,
                    'pass_score' => $assignmentData['pass_score'] ?? 50,
                ]);
            }
        }

        // LƯU TÀI LIỆU ĐÍNH KÈM
        if ($request->has('documents')) {
            foreach ($request->documents as $doc) {
                if ($doc['type'] === 'file' && isset($doc['file'])) {
                    $path = $doc['file']->store('course_documents', 's3');
                    $course->documents()->create([
                        'title' => $doc['title'] ?? $doc['file']->getClientOriginalName(),
                        'type' => 'file',
                        'file_path' => $path,
                    ]);
                } elseif ($doc['type'] === 'link' && isset($doc['url'])) {
                    $course->documents()->create([
                        'title' => $doc['title'] ?? 'Tài liệu tham khảo',
                        'type' => 'link',
                        'url' => $doc['url'],
                    ]);
                }
            }
        }

        return redirect()->route('system.courses.index')->with('success', 'Đã tạo khóa học thành công!');
    }
    
    public function show(Course $course)
    {
        return Inertia::render('SystemAdmin/Courses/Show', [
            'course' => $course
        ]);
    }

    public function edit(Course $course)
    {
        $course->load('documents', 'departments', 'lessons', 'assignments'); 
        return Inertia::render('SystemAdmin/Courses/Edit', [
            'course' => $course,
            'departments' => Department::all() 
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_audience' => 'required|string',
            'duration' => 'required|numeric',
            'format' => 'required|string',
            'instructor' => 'nullable|string',
            'notes' => 'nullable|string',
            'description' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $course->update($validated);

        // CẬP NHẬT BÀI GIẢNG (LESSONS)
        if ($request->has('deleted_lesson_ids')) {
            $lessonsToDelete = $course->lessons()->whereIn('id', $request->deleted_lesson_ids)->get();
            foreach ($lessonsToDelete as $lessonToDelete) {
                if (in_array($lessonToDelete->media_type, ['video_upload', 'slide_pdf']) && $lessonToDelete->media_url) {
                    Storage::disk('s3')->delete($lessonToDelete->media_url);
                }
                $lessonToDelete->delete();
            }
        }
        if ($request->has('lessons') && is_array($request->lessons)) {
            foreach ($request->lessons as $index => $lessonData) {
                $lessonId = $lessonData['id'] ?? null;
                $mediaUrl = $lessonData['media_url'] ?? null;
                if (in_array($lessonData['media_type'], ['video_upload', 'slide_pdf']) && isset($lessonData['file']) && $lessonData['file'] !== 'replace') {
                    $mediaUrl = $lessonData['file']->store('course_lessons', 's3');
                }
                $updateData = [
                    'title' => $lessonData['title'],
                    'media_type' => $lessonData['media_type'],
                    'duration' => $lessonData['duration'] ?? 0,
                    'order_num' => $index + 1,
                ];
                if ($mediaUrl || $lessonData['media_type'] === 'youtube') {
                    $updateData['media_url'] = $mediaUrl;
                }

                if ($lessonId && isset($lessonData['is_existing']) && $lessonData['is_existing']) {
                    $course->lessons()->where('id', $lessonId)->update($updateData);
                } else {
                    $course->lessons()->create($updateData);
                }
            }
        }

        // CẬP NHẬT BÀI TẬP / ĐỀ BÀI (ASSIGNMENTS)
        if ($request->has('deleted_assignment_ids')) {
            $course->assignments()->whereIn('id', $request->deleted_assignment_ids)->delete();
        }
        if ($request->has('assignments') && is_array($request->assignments)) {
            foreach ($request->assignments as $assignmentData) {
                $assignmentId = $assignmentData['id'] ?? null;
                
                $questionsJson = isset($assignmentData['questions']) && is_array($assignmentData['questions']) 
                    ? json_encode($assignmentData['questions'], JSON_UNESCAPED_UNICODE) 
                    : json_encode([$assignmentData['content'] ?? ''], JSON_UNESCAPED_UNICODE);

                $updateData = [
                    'title' => $assignmentData['title'],
                    'type' => $assignmentData['type'],
                    'content' => $questionsJson,
                    'pass_score' => $assignmentData['pass_score'] ?? 50,
                ];

                if ($assignmentId && isset($assignmentData['is_existing']) && $assignmentData['is_existing']) {
                    $course->assignments()->where('id', $assignmentId)->update($updateData);
                } else {
                    $course->assignments()->create($updateData);
                }
            }
        }

        // CẬP NHẬT TÀI LIỆU (DOCUMENTS)
        if ($request->has('new_documents')) {
            foreach ($request->new_documents as $doc) {
                if ($doc['type'] === 'file' && isset($doc['file'])) {
                    $path = $doc['file']->store('course_documents', 's3');
                    $course->documents()->create([
                        'title' => $doc['title'] ?? $doc['file']->getClientOriginalName(),
                        'type' => 'file',
                        'file_path' => $path,
                    ]);
                } elseif ($doc['type'] === 'link' && isset($doc['url'])) {
                    $course->documents()->create(['title' => $doc['title'] ?? 'Tài liệu tham khảo', 'type' => 'link', 'url' => $doc['url']]);
                }
            }
        }
        if ($request->has('deleted_document_ids')) {
            $docsToDelete = $course->documents()->whereIn('id', $request->deleted_document_ids)->get();
            foreach ($docsToDelete as $docToDelete) {
                if ($docToDelete->type === 'file' && $docToDelete->file_path) {
                    Storage::disk('s3')->delete($docToDelete->file_path);
                }
                $docToDelete->delete();
            }
        }

        return redirect()->route('system.courses.index')->with('success', 'Đã cập nhật thông tin khóa học thành công!');
    }

    public function destroy(Course $course)
    {
        foreach ($course->documents as $doc) {
            if ($doc->type === 'file' && $doc->file_path) {
                Storage::disk('s3')->delete($doc->file_path);
            }
        }
        foreach ($course->lessons as $lesson) {
            if (in_array($lesson->media_type, ['video_upload', 'slide_pdf']) && $lesson->media_url) {
                Storage::disk('s3')->delete($lesson->media_url);
            }
        }
        $course->delete();
        return redirect()->route('system.courses.index')->with('success', 'Đã xóa khóa học an toàn!');
    }
}