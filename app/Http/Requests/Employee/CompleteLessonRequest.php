<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class CompleteLessonRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'lesson_id' => 'required|exists:course_lessons,id'
        ];
    }

    public function messages()
    {
        return [
            'lesson_id.required' => 'Không tìm thấy thông tin bài học.',
            'lesson_id.exists' => 'Bài học này không tồn tại trên hệ thống.'
        ];
    }
}