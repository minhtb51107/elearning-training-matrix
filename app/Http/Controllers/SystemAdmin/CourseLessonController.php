<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseLessonController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' => 'required|string', 'media_type' => 'required|string',
            'media_url' => 'nullable|string', 'duration' => 'nullable|integer', 'file' => 'nullable|file'
        ]);

        if ($request->hasFile('file') && in_array($data['media_type'], ['video_upload', 'slide_pdf'])) {
            $data['media_url'] = $request->file('file')->store('course_lessons', 's3');
        }

        $data['order_num'] = $course->lessons()->count() + 1;
        $course->lessons()->create($data);
        return back()->with('success', 'Đã thêm bài giảng thành công!');
    }

    public function update(Request $request, Course $course, CourseLesson $lesson)
    {
        $data = $request->validate([
            'title' => 'required|string', 'media_type' => 'required|string',
            'media_url' => 'nullable|string', 'duration' => 'nullable|integer', 'file' => 'nullable|file'
        ]);

        if ($request->hasFile('file') && in_array($data['media_type'], ['video_upload', 'slide_pdf'])) {
            if ($lesson->media_url) Storage::disk('s3')->delete($lesson->media_url);
            $data['media_url'] = $request->file('file')->store('course_lessons', 's3');
        }

        $lesson->update($data);
        return back()->with('success', 'Đã cập nhật bài giảng!');
    }

    public function destroy(Course $course, CourseLesson $lesson)
    {
        if (in_array($lesson->media_type, ['video_upload', 'slide_pdf']) && $lesson->media_url) {
            Storage::disk('s3')->delete($lesson->media_url);
        }
        $lesson->delete();
        return back()->with('success', 'Đã xóa bài giảng!');
    }
}