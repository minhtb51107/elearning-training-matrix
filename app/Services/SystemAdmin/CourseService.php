<?php

namespace App\Services\SystemAdmin;

use App\Models\Course;
use App\Models\TrainingRequest;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($data) {
            $lastCourse = Course::latest('id')->first();
            $nextId = $lastCourse ? $lastCourse->id + 1 : 1;
            $data['code'] = 'KH-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            $data['status'] = 'Chưa có lớp';

            $course = Course::create(collect($data)->except(['request_ids', 'lessons', 'assignments', 'documents'])->toArray());

            // Gắn kết Request từ Phòng ban
            if (!empty($data['request_ids'])) {
                $requests = TrainingRequest::whereIn('id', $data['request_ids'])->get();
                
                TrainingRequest::whereIn('id', $data['request_ids'])->update([
                    'status' => 'processed',
                    'course_id' => $course->id,
                    'updated_at' => now()
                ]);
                
                $course->departments()->sync($requests->pluck('department_id')->toArray());

                foreach ($requests as $req) {
                    $requester = User::find($req->requester_id);
                    if ($requester) {
                        $requester->notify(new SystemNotification(
                            'Khóa học đã được triển khai',
                            "Yêu cầu <strong>{$req->course_name}</strong> của bạn đã được Admin tạo thành khóa học chính thức.",
                            route('department.courses.show', $course->id)
                        ));
                    }
                }
            }

            // Xử lý bài giảng
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

            // Xử lý bài tập
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

            // Xử lý tài liệu
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
                        $course->documents()->create([
                            'title' => $doc['title'] ?? 'Tài liệu tham khảo',
                            'type' => 'link',
                            'url' => $doc['url'],
                        ]);
                    }
                }
            }

            return $course;
        });
    }

    public function updateCourse(Course $course, array $data)
    {
        DB::transaction(function () use ($course, $data) {
            $course->update(collect($data)->except(['deleted_lesson_ids', 'lessons', 'deleted_assignment_ids', 'assignments', 'new_documents', 'deleted_document_ids'])->toArray());

            // Cập nhật Bài giảng
            if (!empty($data['deleted_lesson_ids'])) {
                $lessonsToDelete = $course->lessons()->whereIn('id', $data['deleted_lesson_ids'])->get();
                foreach ($lessonsToDelete as $lessonToDelete) {
                    if (in_array($lessonToDelete->media_type, ['video_upload', 'slide_pdf']) && $lessonToDelete->media_url) {
                        Storage::disk('s3')->delete($lessonToDelete->media_url);
                    }
                    $lessonToDelete->delete();
                }
            }
            if (!empty($data['lessons'])) {
                foreach ($data['lessons'] as $index => $lessonData) {
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

            // Cập nhật Bài tập
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

            // Cập nhật Tài liệu
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
                        Storage::disk('s3')->delete($docToDelete->file_path);
                    }
                    $docToDelete->delete();
                }
            }
        });
    }

    public function deleteCourse(Course $course)
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
    }
}