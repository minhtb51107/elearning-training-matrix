<?php

namespace App\Services\SystemAdmin;

use App\Models\Submission;
use App\Models\ClassEnrollment;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Enums\EnrollmentStatusEnum; // Tuỳ thuộc vào thư mục bạn đang lưu Enum

class GradeService
{
    public function getPendingSubmissions()
    {
        $submissions = Submission::with(['user.department', 'courseClass.course', 'assignment'])
            ->where('status', 'pending') 
            ->latest()
            ->paginate(15);

        $formattedSubmissions = $submissions->getCollection()->map(function ($sub, $index) {
            return [
                'id' => $sub->id,
                'stt' => $index + 1,
                'emp_code' => $sub->user->code ?? 'NV-'.$sub->user->id,
                'name' => $sub->user->name,
                'department' => $sub->user->department->name ?? '--',
                'course' => $sub->courseClass->course->name ?? '--',
                'class_code' => $sub->courseClass->code,
                'type' => $sub->assignment->type === 'final' ? 'Cuối khóa' : ($sub->assignment->type === 'mid_term' ? 'Giữa khóa' : 'Thực hành'),
                'submit_date' => $sub->created_at->format('d/M/Y H:i'),
            ];
        });

        return $submissions->setCollection($formattedSubmissions);
    }

    public function getSubmissionDetail($id)
    {
        $submission = Submission::with(['user.department', 'courseClass.course', 'assignment'])->findOrFail($id);

        $fileUrl = null;
        if ($submission->file_url) {
            $fileUrl = str_contains($submission->file_url, 'http') ? $submission->file_url : Storage::disk('s3')->url($submission->file_url);
        }

        $questions = json_decode($submission->assignment->content, true);
        if (!is_array($questions)) $questions = [$submission->assignment->content];

        $answers = json_decode($submission->content, true);
        if (!is_array($answers)) $answers = [$submission->content];

        return [
            'id' => $submission->id,
            'student_name' => $submission->user->name,
            'emp_code' => $submission->user->code ?? 'NV-'.$submission->user->id,
            'department' => $submission->user->department->name ?? '--',
            'submit_date' => $submission->created_at->format('d/M/Y H:i'),
            'course' => $submission->courseClass->course->name ?? '--',
            'class_code' => $submission->courseClass->code,
            'type' => $submission->assignment->type === 'final' ? 'BÀI KIỂM TRA CUỐI KHÓA' : 'BÀI KIỂM TRA/THỰC HÀNH',
            'questions' => $questions,
            'answers' => $answers,
            'pass_score' => $submission->assignment->pass_score,
            'student_file' => $fileUrl,
            'score' => $submission->score,
            'feedback' => $submission->feedback,
        ];
    }

    public function gradeSubmission($id, array $data, $graderId)
    {
        DB::transaction(function () use ($id, $data, $graderId) {
            $submission = Submission::with('assignment', 'courseClass')->findOrFail($id);

            $submission->update([
                'score' => $data['score'],
                'feedback' => $data['feedback'],
                'status' => 'graded',
                'graded_by' => $graderId
            ]);

            $isPassed = $data['score'] >= $submission->assignment->pass_score;

            if ($submission->assignment->type === 'final') {
                $enrollment = ClassEnrollment::where('course_class_id', $submission->course_class_id)
                    ->where('user_id', $submission->user_id)
                    ->first();

                if ($enrollment) {
                    // 👉 Đã đổi chuỗi trạng thái đỗ/trượt thành Enum
                    $newStatus = $isPassed ? EnrollmentStatusEnum::COMPLETED->value : EnrollmentStatusEnum::FAILED->value;
                    $enrollment->update(['status' => $newStatus]);
                }
            }

            $student = User::find($submission->user_id);
            if ($student) {
                $statusText = $isPassed ? 'ĐẠT' : 'CHƯA ĐẠT';
                $student->notify(new SystemNotification(
                    'Kết quả bài kiểm tra',
                    "Bài kiểm tra lớp <strong>{$submission->courseClass->name}</strong> đã có điểm. Bạn đạt <strong>{$data['score']}</strong> điểm ({$statusText}).",
                    route('employee.my-classes.show', $submission->course_class_id)
                ));
            }
        });
    }
}