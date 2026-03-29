<?php

namespace App\Services\SystemAdmin;

use App\Models\Course;
use App\Models\TrainingRequest;
use App\Models\User;
use App\Models\QuizQuestion;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Enums\RequestStatusEnum;

class CourseService
{
    public function getFilteredCourses(array $filters)
    {
        $query = Course::latest();

        if (!empty($filters['tab']) && $filters['tab'] !== 'all') {
            $query->where('status', $filters['tab']);
        }
        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }
        if (!empty($filters['scope'])) {
            $query->where('target_audience', $filters['scope']);
        }
        if (!empty($filters['source'])) {
            if ($filters['source'] === 'Yêu cầu') {
                $query->whereNull('reason'); 
            } elseif ($filters['source'] === 'Nội bộ') {
                $query->whereNotNull('reason'); 
            }
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'Chưa có lớp') {
                $query->where('status', 'Chưa có lớp');
            } elseif ($filters['status'] === 'Có lớp') {
                $query->where('status', '!=', 'Chưa có lớp');
            }
        }
        if (!empty($filters['keyword'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        return $query->paginate(15)->withQueryString();
    }

    public function createCourse(array $data)
    {
        $requestsToNotify = collect();

        $course = DB::transaction(function () use ($data, &$requestsToNotify) {
            
            // Dùng UUID tạm thời để tránh trùng, sau khi insert có ID chính xác mới update lại code.
            $data['code'] = 'TEMP-' . Str::uuid();
            $data['status'] = 'Chưa có lớp';

            $course = Course::create(collect($data)->except([
                'request_ids', 'lessons', 'assignments', 'documents', 'instructor', 'notes',
                'quizzes', 'deleted_quiz_ids', 'deleted_quiz_question_ids'
            ])->toArray());
            
            // Cập nhật lại Code chuẩn với ID được cấp từ DB
            $course->update(['code' => 'KH-' . date('Y') . '-' . str_pad($course->id, 4, '0', STR_PAD_LEFT)]);

            if (!empty($data['request_ids'])) {
                $requests = TrainingRequest::whereIn('id', $data['request_ids'])->get();
                
                TrainingRequest::whereIn('id', $data['request_ids'])->update([
                    'status' => RequestStatusEnum::PROCESSED->value,
                    'course_id' => $course->id,
                    'updated_at' => now()
                ]);
                
                $course->departments()->sync($requests->pluck('department_id')->toArray());
                $requestsToNotify = $requests; 
            }

            // Bài giảng
            if (!empty($data['lessons'])) {
                foreach ($data['lessons'] as $index => $lessonData) {
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

            // Bài tập Tự luận
            if (!empty($data['assignments'])) {
                foreach ($data['assignments'] as $assignmentData) {
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

            // Bài thi Trắc nghiệm
            if (!empty($data['quizzes'])) {
                foreach ($data['quizzes'] as $quizData) {
                    $quiz = $course->quizzes()->create([
                        'title' => $quizData['title'],
                        'duration_minutes' => $quizData['duration_minutes'],
                        'pass_score' => $quizData['pass_score'],
                    ]);

                    if (!empty($quizData['questions'])) {
                        foreach ($quizData['questions'] as $qData) {
                            $question = $quiz->questions()->create([
                                'question_text' => $qData['question_text'],
                                'type' => $qData['type'],
                                'points' => $qData['points'],
                            ]);

                            if (!empty($qData['options'])) {
                                foreach ($qData['options'] as $optData) {
                                    $question->options()->create([
                                        'option_text' => $optData['option_text'],
                                        'is_correct' => $optData['is_correct'],
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Tài liệu
            if (!empty($data['documents'])) {
                foreach ($data['documents'] as $doc) {
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

            return $course;
        });

        // Đẩy việc gửi thông báo (chạy chậm) ra khỏi Database Transaction
        foreach ($requestsToNotify as $req) {
            $requester = User::find($req->requester_id);
            if ($requester) {
                $requester->notify(new SystemNotification(
                    'Khóa học đã được triển khai',
                    "Yêu cầu <strong>{$req->course_name}</strong> của bạn đã được Admin tạo thành khóa học chính thức.",
                    route('department.courses.show', $course->id)
                ));
            }
        }

        return $course;
    }

    public function updateCourse(Course $course, array $data)
    {
        DB::transaction(function () use ($course, $data) {
            $exceptFields = [
                'deleted_lesson_ids', 'lessons', 
                'deleted_assignment_ids', 'assignments', 
                'new_documents', 'deleted_document_ids', 
                'instructor', 'notes',
                'quizzes', 'deleted_quiz_ids', 'deleted_quiz_question_ids'
            ];
            
            $course->update(collect($data)->except($exceptFields)->toArray());

            // 1. CẬP NHẬT BÀI GIẢNG
            if (!empty($data['deleted_lesson_ids'])) {
                $lessonsToDelete = $course->lessons()->whereIn('id', $data['deleted_lesson_ids'])->get();
                foreach ($lessonsToDelete as $lessonToDelete) {
                    if (in_array($lessonToDelete->media_type, ['video_upload', 'slide_pdf']) && $lessonToDelete->media_url) {
                        try { Storage::disk('s3')->delete($lessonToDelete->media_url); } catch (\Exception $e) { Log::error("S3 Delete Lesson Error: " . $e->getMessage()); }
                    }
                    $lessonToDelete->delete();
                }
            }
            
            if (!empty($data['lessons'])) {
                foreach ($data['lessons'] as $index => $lessonData) {
                    $lessonId = $lessonData['id'] ?? null;
                    $mediaUrl = $lessonData['media_url'] ?? null;

                    // Nếu tải lên file mới đè bài cũ, xóa file cũ trên S3
                    if ($lessonId && isset($lessonData['is_existing']) && $lessonData['is_existing']) {
                        $existingLesson = $course->lessons()->find($lessonId);
                        if (in_array($lessonData['media_type'], ['video_upload', 'slide_pdf']) && isset($lessonData['file']) && $lessonData['file'] !== 'replace') {
                            if ($existingLesson && $existingLesson->media_url) {
                                try { Storage::disk('s3')->delete($existingLesson->media_url); } catch (\Exception $e) { Log::error("S3 Delete Old Lesson Error: " . $e->getMessage()); }
                            }
                            $mediaUrl = $lessonData['file']->store('course_lessons', 's3');
                        }
                    } else {
                        if (in_array($lessonData['media_type'], ['video_upload', 'slide_pdf']) && isset($lessonData['file']) && $lessonData['file'] !== 'replace') {
                            $mediaUrl = $lessonData['file']->store('course_lessons', 's3');
                        }
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

            // 2. CẬP NHẬT BÀI TẬP TỰ LUẬN
            if (!empty($data['deleted_assignment_ids'])) {
                $course->assignments()->whereIn('id', $data['deleted_assignment_ids'])->delete();
            }
            
            if (!empty($data['assignments'])) {
                foreach ($data['assignments'] as $assignmentData) {
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

            // 3. CẬP NHẬT BÀI TRẮC NGHIỆM (QUIZZES)
            if (!empty($data['deleted_quiz_ids'])) {
                $course->quizzes()->whereIn('id', $data['deleted_quiz_ids'])->delete();
            }
            
            if (!empty($data['deleted_quiz_question_ids'])) {
                QuizQuestion::whereIn('id', $data['deleted_quiz_question_ids'])->delete();
            }

            if (!empty($data['quizzes'])) {
                foreach ($data['quizzes'] as $quizData) {
                    $quizId = $quizData['id'] ?? null;
                    
                    if ($quizId && isset($quizData['is_existing']) && $quizData['is_existing']) {
                        $quiz = $course->quizzes()->find($quizId);
                        if ($quiz) {
                            $quiz->update([
                                'title' => $quizData['title'],
                                'duration_minutes' => $quizData['duration_minutes'],
                                'pass_score' => $quizData['pass_score'],
                            ]);
                        }
                    } else {
                        $quiz = $course->quizzes()->create([
                            'title' => $quizData['title'],
                            'duration_minutes' => $quizData['duration_minutes'],
                            'pass_score' => $quizData['pass_score'],
                        ]);
                    }

                    if ($quiz && !empty($quizData['questions'])) {
                        foreach ($quizData['questions'] as $qData) {
                            $qId = $qData['id'] ?? null;
                            
                            if ($qId && isset($qData['is_existing']) && $qData['is_existing']) {
                                $question = $quiz->questions()->find($qId);
                                if ($question) {
                                    $question->update([
                                        'question_text' => $qData['question_text'],
                                        'type' => $qData['type'],
                                        'points' => $qData['points'],
                                    ]);
                                }
                            } else {
                                $question = $quiz->questions()->create([
                                    'question_text' => $qData['question_text'],
                                    'type' => $qData['type'],
                                    'points' => $qData['points'],
                                ]);
                            }

                            // Xử lý Đáp án (Options)
                            if ($question && !empty($qData['options'])) {
                                $passedOptionIds = collect($qData['options'])
                                    ->filter(fn($o) => !empty($o['id']) && !empty($o['is_existing']))
                                    ->pluck('id')
                                    ->toArray();
                                    
                                $question->options()->whereNotIn('id', $passedOptionIds)->delete();

                                foreach ($qData['options'] as $optData) {
                                    $optId = $optData['id'] ?? null;
                                    
                                    if ($optId && !empty($optData['is_existing'])) {
                                        $question->options()->where('id', $optId)->update([
                                            'option_text' => $optData['option_text'],
                                            'is_correct' => $optData['is_correct'],
                                        ]);
                                    } else {
                                        $question->options()->create([
                                            'option_text' => $optData['option_text'],
                                            'is_correct' => $optData['is_correct'],
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // 4. CẬP NHẬT TÀI LIỆU
            if (!empty($data['new_documents'])) {
                foreach ($data['new_documents'] as $doc) {
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
            
            if (!empty($data['deleted_document_ids'])) {
                $docsToDelete = $course->documents()->whereIn('id', $data['deleted_document_ids'])->get();
                foreach ($docsToDelete as $docToDelete) {
                    if ($docToDelete->type === 'file' && $docToDelete->file_path) {
                        try { Storage::disk('s3')->delete($docToDelete->file_path); } catch (\Exception $e) { Log::error("S3 Delete Doc Error: " . $e->getMessage()); }
                    }
                    $docToDelete->delete();
                }
            }
        });
    }

    public function deleteCourse(Course $course)
    {
        // KHÔNG XÓA CỨNG FILE Ở ĐÂY để tương thích với Soft Delete của Database
        $course->delete();
    }
}