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
            'target_audience' => 'required|string',
            'duration' => 'required|numeric|min:0',
            'format' => 'required|string',
            'instructor' => 'nullable|string',
            'notes' => 'nullable|string',
            'description' => 'nullable|string',
            'reason' => 'nullable|string',
            // Các trường động cũng cần khai báo để hàm validated() có thể lấy được
            'request_ids' => 'nullable|array',
            'lessons' => 'nullable|array',
            'assignments' => 'nullable|array',
            'documents' => 'nullable|array',
            'new_documents' => 'nullable|array',
            'deleted_lesson_ids' => 'nullable|array',
            'deleted_assignment_ids' => 'nullable|array',
            'deleted_document_ids' => 'nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên khóa học.',
            'duration.required' => 'Vui lòng nhập thời lượng khóa học.',
            'duration.numeric' => 'Thời lượng phải là dạng số.',
        ];
    }
}