<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chứng chỉ - {{ $student_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .cert-container {
            border: 10px solid #1e3a8a;
            padding: 40px;
            text-align: center;
            position: relative;
            height: 550px;
        }
        .cert-inner {
            border: 2px solid #fbbf24;
            padding: 20px;
            height: 100%;
            box-sizing: border-box;
        }
        .header {
            font-size: 28px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .sub-header {
            font-size: 16px;
            color: #666;
            margin-bottom: 40px;
        }
        .presented-to {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .student-name {
            font-size: 36px;
            font-weight: bold;
            color: #d97706;
            text-transform: uppercase;
            margin-bottom: 30px;
            border-bottom: 2px dashed #ccc;
            display: inline-block;
            padding-bottom: 5px;
            min-width: 400px;
        }
        .course-label {
            font-size: 18px;
            margin-bottom: 15px;
        }
        .course-name {
            font-size: 24px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 40px;
        }
        .footer-info {
            font-size: 14px;
            margin-top: 50px;
        }
        .score { font-weight: bold; color: #16a34a; }
        .cert-id {
            position: absolute;
            bottom: 30px;
            left: 50px;
            font-size: 12px;
            color: #888;
        }
        .signature {
            position: absolute;
            bottom: 30px;
            right: 50px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 150px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="cert-container">
        <div class="cert-inner">
            <div class="header">CHỨNG NHẬN HOÀN THÀNH KHÓA HỌC</div>
            <div class="sub-header">Hệ thống Đào tạo Nội bộ (Training Matrix)</div>
            
            <div class="presented-to">Chứng nhận học viên:</div>
            <div class="student-name">{{ $student_name }}</div>
            
            <div class="course-label">Đã hoàn thành xuất sắc chương trình đào tạo:</div>
            <div class="course-name">{{ $course_name }}</div>
            
            <div class="footer-info">
                <span>Lớp: <b>{{ $class_code }}</b></span> &nbsp; | &nbsp; 
                <span>Điểm tổng kết: <span class="score">{{ $score }}/100</span></span> &nbsp; | &nbsp; 
                <span>Ngày cấp: <b>{{ $completed_date }}</b></span>
            </div>

            <div class="cert-id">Mã số: {{ $cert_id }}</div>
            <div class="signature">
                <div class="signature-line"></div>
                <strong>Phòng Đào Tạo</strong>
            </div>
        </div>
    </div>
</body>
</html>