<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'assignment_id' => 'required|exists:assignments,id',
            'answers' => 'required_without:file|array|nullable', 
            'file' => 'nullable|file|max:20480' 
        ];
    }

    public function messages()
    {
        return [
            'assignment_id.required' => 'Không tìm thấy thông tin bài kiểm tra.',
            'assignment_id.exists' => 'Bài kiểm tra không tồn tại trên hệ thống.',
            'answers.required_without' => 'Bạn phải chọn đáp án hoặc đính kèm file bài làm.',
            'file.max' => 'Dung lượng file tải lên không được vượt quá 20MB.'
        ];
    }
}