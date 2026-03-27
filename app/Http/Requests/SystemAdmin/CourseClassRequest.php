<?php

namespace App\Http\Requests\SystemAdmin;

use Illuminate\Foundation\Http\FormRequest;

class CourseClassRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        $rules = [
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'instructor_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_students' => 'required|integer|min:1',
            'format' => 'required|string|in:Online,Offline,Hybrid',
            'department_id' => 'nullable|exists:departments,id',
            'sessions' => 'nullable|array',
            'deleted_session_ids' => 'nullable|array',
        ];

        // Trường 'action' chỉ bắt buộc khi tạo mới (phương thức POST)
        if ($this->isMethod('post')) {
            $rules['action'] = 'required|in:draft,published';
        } else {
            $rules['action'] = 'nullable|in:draft,published';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'course_id.required' => 'Vui lòng chọn một khóa học.',
            'name.required' => 'Tên/Mã lớp không được để trống.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'max_students.min' => 'Số lượng học viên tối thiểu là 1.',
        ];
    }
}