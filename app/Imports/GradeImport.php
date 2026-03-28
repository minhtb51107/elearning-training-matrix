<?php

namespace App\Imports;

use App\Models\Submission;
use App\Services\SystemAdmin\GradeService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class GradeImport implements ToCollection, WithHeadingRow
{
    protected $gradeService;

    public function __construct(GradeService $gradeService)
    {
        $this->gradeService = $gradeService;
    }

    /**
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
        $adminId = Auth::id();

        foreach ($rows as $row) {
            // Đọc các cột dựa trên tên tiêu đề (Heading) đã được "slug hóa" bởi thư viện
            // "Mã bài nộp (KHÔNG SỬA)" sẽ thành "ma_bai_nop_khong_sua"
            $submissionId = $row['ma_bai_nop_khong_sua']; 
            $score = $row['diem_so_0_100'];
            $feedback = $row['nhan_xet_cua_giang_vien'];

            // Nếu giảng viên không nhập điểm dòng này thì bỏ qua
            if (!isset($score) || $score === '') {
                continue; 
            }

            $submission = Submission::find($submissionId);

            if ($submission) {
                // Tái sử dụng lại hàm gradeSubmission đã viết chuẩn từ trước 
                // (nó sẽ tự động cập nhật trạng thái Đạt/Trượt và báo Event cho Học viên)
                $this->gradeService->gradeSubmission(
                    $submissionId, 
                    [
                        'score' => (float) $score,
                        'feedback' => $feedback ?? 'Đã hoàn thành bài kiểm tra.'
                    ], 
                    $adminId
                );
            }
        }
    }
}