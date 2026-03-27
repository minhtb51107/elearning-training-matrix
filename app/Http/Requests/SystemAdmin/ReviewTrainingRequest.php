<?php

namespace App\Http\Requests\SystemAdmin;

use Illuminate\Foundation\Http\FormRequest;

class ReviewTrainingRequest extends FormRequest
{
    public function authorize() { return true; }
public function rules() {
    return [
        'status' => 'required|in:approved,rejected',
        'hr_feedback' => 'required_if:status,rejected|nullable|string' 
    ];
}
public function messages() {
    return ['hr_feedback.required_if' => 'Bạn phải nhập lý do khi từ chối yêu cầu đào tạo này.'];
}
}
