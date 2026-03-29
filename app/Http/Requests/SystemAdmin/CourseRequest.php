<?php

namespace App\Http\Requests\SystemAdmin;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'target_audience' => 'required|string|in:Toàn công ty,Cấp quản lý,Nhân viên mới,Toàn phòng,Cá nhân',
            'duration' => 'required|numeric|min:0',
            'format' => 'required|string',
            'instructor' => 'nullable|string',
            'notes' => 'nullable|string',
            'description' => 'nullable|string',
            'reason' => 'nullable|string',
            'request_ids' => 'nullable|array',
            
            // Bài giảng
            'lessons' => 'nullable|array',
            'deleted_lesson_ids' => 'nullable|array',
            
            // Bài tự luận
            'assignments' => 'nullable|array',
            'deleted_assignment_ids' => 'nullable|array',
            
            // Tài liệu
            'documents' => 'nullable|array',
            'new_documents' => 'nullable|array',
            'deleted_document_ids' => 'nullable|array',
            
            // 👇 THÊM MỚI: Cho phép dữ liệu Trắc nghiệm đi qua Validation
            'quizzes' => 'nullable|array',
            'deleted_quiz_ids' => 'nullable|array',
            'deleted_quiz_question_ids' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên khóa học.',
            'target_audience.in' => 'Đối tượng/Phạm vi đào tạo không hợp lệ.',
            'duration.required' => 'Vui lòng nhập thời lượng khóa học.',
            'duration.numeric' => 'Thời lượng phải là dạng số.',
        ];
    }
}