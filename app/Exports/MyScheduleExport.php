<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class MyScheduleExport implements FromCollection, WithHeadings, WithMapping
{
    protected $schedules;

    public function __construct($schedules)
    {
        $this->schedules = $schedules;
    }

    public function collection()
    {
        return $this->schedules;
    }

    public function headings(): array
    {
        return [
            'STT',
            'Ngày học',
            'Tên Khóa Học',
            'Mã Lớp',
            'Giảng Viên',
            'Hình Thức',
            'Thời Gian',
            'Phòng/Link',
            'Trạng Thái'
        ];
    }

    public function map($session): array
    {
        static $index = 0;
        $index++;
        
        $date = Carbon::parse($session->date)->format('d/m/Y');
        $time = Carbon::parse($session->start_time)->format('H:i') . ' - ' . Carbon::parse($session->end_time)->format('H:i');
        
        $status = 'Chưa diễn ra';
        if (Carbon::now()->format('Y-m-d') === $session->date) $status = 'Đang học';
        if (Carbon::now()->format('Y-m-d') > $session->date) $status = 'Đã kết thúc';

        return [
            $index,
            $date,
            $session->courseClass->course->name ?? 'N/A',
            $session->courseClass->code ?? 'N/A',
            $session->courseClass->instructor->name ?? 'N/A',
            $session->format,
            $time,
            $session->room_or_link ?? '',
            $status
        ];
    }
}