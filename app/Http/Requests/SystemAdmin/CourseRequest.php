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
            // KHÓA VAN: Chỉ cho phép các giá trị đối tượng này lọt qua
            'target_audience' => 'required|string|in:Toàn công ty,Cấp quản lý,Nhân viên mới,Toàn phòng,Cá nhân',
            'duration' => 'required|numeric|min:0',
            'format' => 'required|string',
            'instructor' => 'nullable|string',
            'notes' => 'nullable|string',
            'description' => 'nullable|string',
            'reason' => 'nullable|string',
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
            'target_audience.in' => 'Đối tượng/Phạm vi đào tạo không hợp lệ.',
            'duration.required' => 'Vui lòng nhập thời lượng khóa học.',
            'duration.numeric' => 'Thời lượng phải là dạng số.',
        ];
    }
}