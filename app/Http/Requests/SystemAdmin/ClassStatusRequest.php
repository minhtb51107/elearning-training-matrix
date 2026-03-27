<?php

namespace App\Http\Requests\SystemAdmin;

use Illuminate\Foundation\Http\FormRequest;

class ClassStatusRequest extends FormRequest
{
    public function authorize() { return true; }
public function rules() {
    return ['status' => 'required|string|in:Nháp,Mở đăng ký,Đang học,Kết thúc'];
}
}
