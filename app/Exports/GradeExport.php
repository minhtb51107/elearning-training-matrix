<?php

namespace App\Exports;

use App\Models\Submission;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $courseClassId;

    public function __construct($courseClassId)
    {
        $this->courseClassId = $courseClassId;
    }

    public function collection()
    {
        return Submission::with('user')
            ->where('course_class_id', $this->courseClassId)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Mã bài nộp (KHÔNG SỬA)',
            'Mã Nhân Viên',
            'Tên Nhân Viên',
            'Email',
            'Ngày Nộp Bài',
            'NỘI DUNG BÀI LÀM (TEXT)', // 👉 Thêm cột này
            'LINK FILE ĐÍNH KÈM',      // 👉 Thêm cột này
            'ĐIỂM SỐ (0-100)',
            'NHẬN XÉT CỦA GIẢNG VIÊN'
        ];
    }

    public function map($submission): array
    {
        // Lấy tất cả các cột thực tế đang có trong Database thành dạng mảng
        $attributes = $submission->getAttributes();
        $userAttributes = $submission->user ? $submission->user->getAttributes() : [];

        // 1. Xử lý phần text bài làm
        $textContent = 'Chưa có câu trả lời text.';
        if (array_key_exists('answers', $attributes) && !empty($attributes['answers'])) {
            $answers = is_string($attributes['answers']) ? json_decode($attributes['answers'], true) : $attributes['answers'];
            $textContent = is_array($answers) ? implode("\n\n", $answers) : $answers;
        } elseif (array_key_exists('content', $attributes) && !empty($attributes['content'])) {
            $textContent = $attributes['content'];
        }

        // 2. Xử lý đường dẫn file
        $fileLink = 'Không đính kèm file';
        if (array_key_exists('student_file', $attributes) && !empty($attributes['student_file'])) {
            $fileLink = url($attributes['student_file']); 
        }

        // 3. Xử lý Mã nhân viên (An toàn 100%)
        $userCode = $userAttributes['code'] ?? $userAttributes['emp_code'] ?? 'NV-' . str_pad($submission->user_id, 3, '0', STR_PAD_LEFT);

        // 4. Lấy điểm và lời phê (Nếu DB không có 2 cột này thì để rỗng)
        $score = $attributes['score'] ?? '';
        $feedback = $attributes['feedback'] ?? '';

        return [
            $submission->id,
            $userCode,
            $submission->user->name ?? 'Không rõ',
            $submission->user->email ?? 'Không rõ',
            $submission->created_at ? $submission->created_at->format('d/m/Y H:i') : '',
            $textContent, // Xuất bài làm text
            $fileLink,    // Xuất link file
            $score, 
            $feedback 
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0EA5E9']]],
        ];
    }
}