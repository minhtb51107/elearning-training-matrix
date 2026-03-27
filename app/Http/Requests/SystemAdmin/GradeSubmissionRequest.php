<?php

namespace App\Http\Requests\SystemAdmin;

use Illuminate\Foundation\Http\FormRequest;

class GradeSubmissionRequest extends FormRequest
{
    public function authorize() { return true; }
public function rules() {
    return [
        'score' => 'required|numeric|min:0|max:100',
        'feedback' => 'nullable|string'
    ];
}
public function messages() {
    return ['score.required' => 'Vui lòng nhập điểm số.', 'score.max' => 'Điểm tối đa là 100.'];
}
}
