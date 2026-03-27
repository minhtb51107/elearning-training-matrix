<?php

namespace App\Http\Requests\SystemAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize() { return true; }
public function rules() {
    return [
        'name' => 'required|string|max:255',
        'type' => 'required|in:pdf,link,pptx,doc,video',
        'file' => 'nullable|file|max:20480', 
        'url' => 'nullable|string'
    ];
}
}
