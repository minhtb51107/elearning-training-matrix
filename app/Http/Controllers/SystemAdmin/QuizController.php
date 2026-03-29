<?php

namespace App\Http\Controllers\SystemAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuizController extends Controller
{
    // Hiển thị form tạo bài thi mới
    public function create(Course $course)
    {
        return Inertia::render('SystemAdmin/Quizzes/Form', [
            'course' => $course,
            'quiz' => null
        ]);
    }

    // Lưu bài thi mới
    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'pass_score' => 'required|integer|min:1|max:100',
        ]);

        $course->quizzes()->create($validated);

        return redirect()->route('system.courses.show', $course->id)
            ->with('success', 'Đã tạo bài kiểm tra thành công!');
    }

    // Hiển thị chi tiết bài thi & Quản lý câu hỏi
    public function show(Course $course, Quiz $quiz)
    {
        $quiz->load('questions.options');
        
        return Inertia::render('SystemAdmin/Quizzes/Show', [
            'course' => $course,
            'quiz' => $quiz
        ]);
    }

    // Hiển thị form sửa bài thi
    public function edit(Course $course, Quiz $quiz)
    {
        return Inertia::render('SystemAdmin/Quizzes/Form', [
            'course' => $course,
            'quiz' => $quiz
        ]);
    }

    // Cập nhật thông tin bài thi
    public function update(Request $request, Course $course, Quiz $quiz)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'pass_score' => 'required|integer|min:1|max:100',
        ]);

        $quiz->update($validated);

        return redirect()->route('system.courses.show', $course->id)
            ->with('success', 'Đã cập nhật bài kiểm tra!');
    }

    // Xóa bài thi
    public function destroy(Course $course, Quiz $quiz)
    {
        $quiz->delete();
        return redirect()->route('system.courses.show', $course->id)
            ->with('success', 'Đã xóa bài kiểm tra!');
    }

    // Thêm câu hỏi vào bài thi
    public function storeQuestion(Request $request, Course $course, Quiz $quiz)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:single,multiple',
            'points' => 'required|integer|min:1',
            'options' => 'required|array|min:2',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ]);

        // Kiểm tra xem có ít nhất 1 đáp án đúng không
        $hasCorrect = collect($validated['options'])->contains('is_correct', true);
        if (!$hasCorrect) {
            return back()->withErrors(['options' => 'Phải có ít nhất 1 đáp án đúng.']);
        }

        $question = $quiz->questions()->create([
            'question_text' => $validated['question_text'],
            'type' => $validated['type'],
            'points' => $validated['points'],
        ]);

        foreach ($validated['options'] as $opt) {
            $question->options()->create($opt);
        }

        return back()->with('success', 'Đã thêm câu hỏi mới!');
    }

    // Xóa câu hỏi
    public function destroyQuestion(Course $course, Quiz $quiz, QuizQuestion $question)
    {
        $question->delete();
        return back()->with('success', 'Đã xóa câu hỏi!');
    }
}