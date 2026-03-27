<?php

namespace App\Http\Requests\DepartmentAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền thực hiện request này không.
     */
    public function authorize()
    {
        return true; // Quyền đã được xử lý qua Middleware và Controller
    }

    /**
     * Các luật kiểm tra dữ liệu đầu vào.
     */
    public function rules()
    {
        return [
            'course_name' => 'required|string|max:255',
            'target_audience' => 'required|string|max:255',
            'content' => 'required|string',
            'expected_duration' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'action' => 'required|in:draft,pending'
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi sang Tiếng Việt.
     */
    public function messages()
    {
        return [
            'course_name.required' => 'Vui lòng nhập tên khóa học đề xuất.',
            'course_name.max' => 'Tên khóa học không được vượt quá 255 ký tự.',
            'target_audience.required' => 'Vui lòng xác định đối tượng đào tạo.',
            'content.required' => 'Nội dung đào tạo không được để trống.',
            'expected_duration.required' => 'Vui lòng nhập thời lượng dự kiến.',
            'expected_duration.integer' => 'Thời lượng phải là một số nguyên.',
            'expected_duration.min' => 'Thời lượng đào tạo tối thiểu là 1 giờ.',
            'action.required' => 'Hành động không hợp lệ.',
            'action.in' => 'Trạng thái lưu yêu cầu không hợp lệ.'
        ];
    }
}