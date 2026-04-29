<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán thành công</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #10b981, #059669); padding: 32px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 28px; }
        .header p { margin: 8px 0 0; opacity: 0.9; }
        .body { padding: 32px; }
        .greeting { font-size: 16px; color: #374151; margin-bottom: 20px; }
        .info-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #d1fae5; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #6b7280; font-size: 14px; }
        .info-value { color: #111827; font-weight: 600; font-size: 14px; }
        .amount { color: #059669; font-size: 20px; font-weight: bold; }
        .footer { background: #f9fafb; padding: 20px 32px; text-align: center; color: #9ca3af; font-size: 13px; }
        .btn { display: inline-block; margin-top: 20px; padding: 12px 28px; background: linear-gradient(135deg,#10b981,#059669); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Thanh toán thành công!</h1>
        <p>Gói tin của bạn đã được kích hoạt</p>
    </div>
    <div class="body">
        <p class="greeting">Xin chào <strong>{{ $moiGioi->ten }}</strong>,</p>
        <p>Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi. Thanh toán của bạn đã được xác nhận thành công.</p>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Gói tin đã mua </span>
                <span class="info-value">{{ $goiTin->ten_goi }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Số tin được cấp </span>
                <span class="info-value">{{ $goiTin->so_luong_tin }} tin</span>
            </div>
            <div class="info-row">
                <span class="info-label">Thời hạn sử dụng </span>
                <span class="info-value">{{ $goiTin->so_ngay }} ngày</span>
            </div>
            <div class="info-row">
                <span class="info-label">Mã giao dịch </span>
                <span class="info-value">{{ $giaoDich->ma_giao_dich }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Thời gian thanh toán </span>
                <span class="info-value">{{ $giaoDich->paid_at?->format('H:i d/m/Y') ?? now()->format('H:i d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Số tiền thanh toán </span>
                <span class="info-value amount">{{ number_format($giaoDich->so_tien, 0, ',', '.') }}đ</span>
            </div>
        </div>

        <p>Bạn có thể đăng tin ngay bây giờ. Chúc bạn kinh doanh thành công!</p>
        <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/moi-gioi/dang-tin" class="btn">Đăng tin ngay →</a>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} BĐS Platform. Đây là email tự động, vui lòng không trả lời.</p>
        <p>Nếu bạn có thắc mắc, liên hệ: <a href="mailto:support@bdsplatform.vn">support@bdsplatform.vn</a></p>
    </div>
</div>
</body>
</html>
