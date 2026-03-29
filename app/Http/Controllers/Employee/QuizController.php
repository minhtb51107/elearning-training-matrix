<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\ClassEnrollment;
use App\Enums\EnrollmentStatusEnum; // 👉 Thêm Enum trạng thái
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class QuizController extends Controller
{
    // Màn hình hiển thị Giới thiệu hoặc Đang làm bài
    public function show(CourseClass $courseClass, Quiz $quiz)
    {
        $enrollment = ClassEnrollment::where('course_class_id', $courseClass->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Kiểm tra xem học viên có bài làm nào không
        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('class_enrollment_id', $enrollment->id)
            ->latest()
            ->first();

        // Đã nộp bài thì chuyển thẳng sang trang Kết quả
        if ($attempt && $attempt->status !== 'in_progress') {
            return redirect()->route('employee.my-classes.quizzes.result', [$courseClass->id, $quiz->id, $attempt->id]);
        }

        // Đang thi dở (ví dụ lỡ F5 hoặc rớt mạng) -> Vào thẳng trang làm bài
        if ($attempt && $attempt->status === 'in_progress') {
            $quiz->load('questions.options');
            
            // Ẩn cột 'is_correct' để học viên không f12 gian lận được
            $quiz->questions->each(function($q) {
                $q->options->makeHidden('is_correct');
            });

            return Inertia::render('Employee/MyClasses/Quizzes/Take', [
                'classInfo' => $courseClass,
                'quiz' => $quiz,
                'attempt' => $attempt
            ]);
        }

        // Chưa thi -> Hiện màn hình Intro giới thiệu
        return Inertia::render('Employee/MyClasses/Quizzes/Intro', [
            'classInfo' => $courseClass,
            'quiz' => $quiz
        ]);
    }

    // Học viên bấm "Bắt đầu làm bài"
    public function start(CourseClass $courseClass, Quiz $quiz)
    {
        $enrollment = ClassEnrollment::where('course_class_id', $courseClass->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $existingAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('class_enrollment_id', $enrollment->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$existingAttempt) {
            QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'class_enrollment_id' => $enrollment->id,
                'status' => 'in_progress',
                'started_at' => Carbon::now(),
            ]);
        }

        return redirect()->route('employee.my-classes.quizzes.show', [$courseClass->id, $quiz->id]);
    }

    // Nộp bài và Tự động chấm điểm
    public function submit(Request $request, CourseClass $courseClass, Quiz $quiz)
    {
        $enrollment = ClassEnrollment::where('course_class_id', $courseClass->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('class_enrollment_id', $enrollment->id)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $answers = $request->input('answers', []); 
        $totalScore = 0;
        $maxScore = 0;

        $quiz->load('questions.options');

        foreach ($quiz->questions as $question) {
            $maxScore += $question->points;
            $selectedOptionIds = $answers[$question->id] ?? [];
            if (!is_array($selectedOptionIds)) {
                $selectedOptionIds = [$selectedOptionIds];
            }

            foreach ($selectedOptionIds as $optId) {
                if ($optId) {
                    QuizAttemptAnswer::create([
                        'quiz_attempt_id' => $attempt->id,
                        'quiz_question_id' => $question->id,
                        'quiz_option_id' => $optId
                    ]);
                }
            }

            $correctOptions = $question->options->where('is_correct', true)->pluck('id')->toArray();
            sort($correctOptions);
            sort($selectedOptionIds);

            if (!empty($correctOptions) && $correctOptions == $selectedOptionIds) {
                $totalScore += $question->points;
            }
        }

        $finalScore = $maxScore > 0 ? round(($totalScore / $maxScore) * 100) : 0;
        $isPassed = $finalScore >= $quiz->pass_score;

        $attempt->update([
            'score' => $finalScore,
            'status' => $isPassed ? 'passed' : 'failed',
            'completed_at' => Carbon::now()
        ]);

        // 👉 HYBRID LOGIC: Kiểm tra nếu đậu Quiz và khóa học KHÔNG CÓ bài Tự luận
        if ($isPassed) {
            $hasAssignment = $courseClass->course->assignments()->exists();
            
            if (!$hasAssignment) {
                // Tự động hoàn thành khóa học luôn
                $enrollment->update([
                    'status' => EnrollmentStatusEnum::COMPLETED->value,
                    'final_score' => $finalScore,
                    'completed_at' => Carbon::now()
                ]);
            }
        }

        return redirect()->route('employee.my-classes.quizzes.result', [$courseClass->id, $quiz->id, $attempt->id])
            ->with('success', 'Đã nộp bài thành công!');
    }

    // Màn hình kết quả
    public function result(CourseClass $courseClass, Quiz $quiz, QuizAttempt $attempt)
    {
        if ($attempt->enrollment->user_id !== auth()->id()) {
            abort(403);
        }

        $attempt->load(['answers']);
        $quiz->load('questions.options');

        return Inertia::render('Employee/MyClasses/Quizzes/Result', [
            'classInfo' => $courseClass,
            'quiz' => $quiz,
            'attempt' => $attempt
        ]);
    }
}