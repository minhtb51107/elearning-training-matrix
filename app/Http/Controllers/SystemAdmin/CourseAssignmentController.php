<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Assignment;
use Illuminate\Http\Request;

class CourseAssignmentController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $data = $request->validate([
            'title' => 'required|string', 'type' => 'required|string',
            'pass_score' => 'required|integer', 'questions' => 'required|array'
        ]);

        $course->assignments()->create([
            'title' => $data['title'], 'type' => $data['type'], 'pass_score' => $data['pass_score'],
            'content' => json_encode($data['questions'], JSON_UNESCAPED_UNICODE)
        ]);
        return back()->with('success', 'Đã thêm bài tập tự luận!');
    }

    public function update(Request $request, Course $course, Assignment $assignment)
    {
        $data = $request->validate([
            'title' => 'required|string', 'type' => 'required|string',
            'pass_score' => 'required|integer', 'questions' => 'required|array'
        ]);

        $assignment->update([
            'title' => $data['title'], 'type' => $data['type'], 'pass_score' => $data['pass_score'],
            'content' => json_encode($data['questions'], JSON_UNESCAPED_UNICODE)
        ]);
        return back()->with('success', 'Đã cập nhật bài tập tự luận!');
    }

    public function destroy(Course $course, Assignment $assignment)
    {
        $assignment->delete();
        return back()->with('success', 'Đã xóa bài tập tự luận!');
    }
}