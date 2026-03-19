<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Tạm thời return true, hoặc check role Admin Phòng ban ở đây
        return true; 
    }

    public function rules(): array
    {
        return [
            'course_name' => ['required', 'string', 'max:255'],
            'target_audience' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'expected_duration' => ['required', 'integer', 'min:1'], // Rule bắt buộc > 0
            'notes' => ['nullable', 'string'],
            // Action 'save_draft' hoặc 'submit' để biết trạng thái lưu
            'action' => ['required', 'in:save_draft,submit'] 
        ];
    }

    public function messages(): array
    {
        return [
            'course_name.required' => 'Tên khóa học không được để trống.',
            'target_audience.required' => 'Đối tượng đề xuất không được để trống.',
            'content.required' => 'Nội dung không được để trống.',
            'expected_duration.required' => 'Thời lượng không được để trống.',
            'expected_duration.min' => 'Thời lượng phải lớn hơn 0.',
        ];
    }
}