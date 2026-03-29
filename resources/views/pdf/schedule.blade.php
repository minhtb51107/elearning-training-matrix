<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch Học Của Tôi</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        h2 { text-align: center; text-transform: uppercase; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2>Lịch Học Của Tôi</h2>
    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Ngày học</th>
                <th>Tên Khóa Học</th>
                <th>Mã Lớp</th>
                <th>Giảng Viên</th>
                <th>Hình Thức</th>
                <th>Thời Gian</th>
                <th>Phòng/Link</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedules as $index => $session)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($session->date)->format('d/m/Y') }}</td>
                    <td>{{ $session->courseClass->course->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $session->courseClass->code ?? 'N/A' }}</td>
                    <td>{{ $session->courseClass->instructor->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $session->format }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}</td>
                    <td>{{ $session->room_or_link }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>