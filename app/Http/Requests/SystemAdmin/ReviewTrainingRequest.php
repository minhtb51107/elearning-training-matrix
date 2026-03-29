<?php

namespace App\Http\Requests\SystemAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Thêm dòng này để import Rule
use App\Enums\RequestStatusEnum; // Thêm dòng này để import Enum

class ReviewTrainingRequest extends FormRequest
{
    public function authorize() 
    { 
        return true; 
    }
    
    public function rules()
    {
        return [
            'status' => [
                'required', 
                Rule::in([RequestStatusEnum::APPROVED->value, RequestStatusEnum::REJECTED->value])
            ],
            'hr_feedback' => 'required_if:status,' . RequestStatusEnum::REJECTED->value . '|nullable|string' 
        ];
    }
    
    public function messages() 
    {
        return [
            'hr_feedback.required_if' => 'Bạn phải nhập lý do khi từ chối yêu cầu đào tạo này.'
        ];
    }
}