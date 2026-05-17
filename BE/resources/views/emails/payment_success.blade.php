<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán thành công</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                {{-- HEADER --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);padding:36px 40px;text-align:center;">
                        <div style="display:inline-block;background:rgba(255,255,255,0.2);border-radius:50%;width:72px;height:72px;line-height:72px;font-size:36px;margin-bottom:16px;">✅</div>
                        <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:800;">Thanh toán thành công!</h1>
                        <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;">Gói tin đã được kích hoạt ngay lập tức</p>
                    </td>
                </tr>

                {{-- GREETING --}}
                <tr>
                    <td style="padding:32px 40px 8px;">
                        <p style="margin:0 0 6px;color:#374151;font-size:16px;">Xin chào <strong>{{ $moiGioi->ten }}</strong>,</p>
                        <p style="margin:0;color:#6b7280;font-size:15px;line-height:1.6;">
                            Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi. 
                            Giao dịch của bạn đã được xác nhận thành công và gói tin đã được kích hoạt.
                        </p>
                    </td>
                </tr>

                {{-- TRANSACTION INFO --}}
                <tr>
                    <td style="padding:20px 40px 8px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-radius:10px;overflow:hidden;border:1px solid #bbf7d0;">
                            <tr>
                                <td style="background:#065f46;padding:12px 20px;">
                                    <p style="margin:0;color:#ffffff;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;">📋 Thông tin giao dịch</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="background:#f0fdf4;padding:4px 0;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding:10px 20px;border-bottom:1px solid #d1fae5;">
                                                <table width="100%" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td style="color:#6b7280;font-size:13px;width:50%;">Mã giao dịch</td>
                                                        <td style="color:#111827;font-size:13px;font-weight:700;text-align:right;font-family:'Courier New',monospace;">{{ $giaoDich->ma_giao_dich }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:10px 20px;border-bottom:1px solid #d1fae5;">
                                                <table width="100%" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td style="color:#6b7280;font-size:13px;width:50%;">Thời gian thanh toán</td>
                                                        <td style="color:#111827;font-size:13px;font-weight:600;text-align:right;">{{ $giaoDich->paid_at?->format('H:i - d/m/Y') ?? now()->format('H:i - d/m/Y') }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:10px 20px;border-bottom:1px solid #d1fae5;">
                                                <table width="100%" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td style="color:#6b7280;font-size:13px;width:50%;">Phương thức</td>
                                                        <td style="color:#111827;font-size:13px;font-weight:600;text-align:right;">{{ strtoupper($giaoDich->phuong_thuc ?? 'SePay') }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:12px 20px;background:#dcfce7;">
                                                <table width="100%" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td style="color:#065f46;font-size:14px;font-weight:700;width:50%;">Số tiền thanh toán</td>
                                                        <td style="color:#059669;font-size:20px;font-weight:800;text-align:right;">{{ number_format($giaoDich->so_tien, 0, ',', '.') }}đ</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- PACKAGE INFO --}}
                <tr>
                    <td style="padding:16px 40px 24px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-radius:10px;overflow:hidden;border:1px solid #e5e7eb;">
                            <tr>
                                <td style="background:#1f2937;padding:12px 20px;">
                                    <p style="margin:0;color:#ffffff;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;">🎁 Gói tin đã kích hoạt: {{ $goiTin->ten_goi }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td width="33%" style="padding:16px 12px;text-align:center;border-right:1px solid #e5e7eb;">
                                                <p style="margin:0;color:#10b981;font-size:28px;font-weight:800;">{{ $goiTin->so_luong_tin }}</p>
                                                <p style="margin:4px 0 0;color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Lượt đăng tin</p>
                                            </td>
                                            <td width="33%" style="padding:16px 12px;text-align:center;border-right:1px solid #e5e7eb;">
                                                <p style="margin:0;color:#10b981;font-size:28px;font-weight:800;">{{ $goiTin->so_ngay }}</p>
                                                <p style="margin:4px 0 0;color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Ngày hiệu lực</p>
                                            </td>
                                            <td width="33%" style="padding:16px 12px;text-align:center;">
                                                <p style="margin:0;color:#10b981;font-size:22px;font-weight:800;">{{ $goiTin->gan_nhan_vip ? 'VIP' : 'STD' }}</p>
                                                <p style="margin:4px 0 0;color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;">Hạng tin</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 20px;background:#f9fafb;border-top:1px solid #e5e7eb;">
                                    <p style="margin:0;color:#6b7280;font-size:13px;">
                                        📅 Hạn sử dụng đến: <strong style="color:#374151;">{{ now()->addDays($goiTin->so_ngay)->format('d/m/Y') }}</strong>
                                        &nbsp;&nbsp;|&nbsp;&nbsp;
                                        📧 Tài khoản: <strong style="color:#374151;">{{ $moiGioi->email }}</strong>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- CTA --}}
                <tr>
                    <td style="padding:0 40px 36px;text-align:center;">
                        <p style="margin:0 0 16px;color:#374151;font-size:15px;">Bạn có thể bắt đầu đăng tin ngay bây giờ!</p>
                        <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/moi-gioi/dang-tin"
                           style="display:inline-block;padding:14px 40px;background:linear-gradient(135deg,#10b981,#059669);color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:700;">
                            🏠 Đăng tin ngay →
                        </a>
                    </td>
                </tr>

                {{-- DIVIDER --}}
                <tr><td style="padding:0 40px;"><div style="height:1px;background:#e5e7eb;"></div></td></tr>

                {{-- SUPPORT --}}
                <tr>
                    <td style="padding:20px 40px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border-left:4px solid #3b82f6;border-radius:0 8px 8px 0;">
                            <tr>
                                <td style="padding:14px 18px;">
                                    <p style="margin:0;color:#1e40af;font-size:13px;line-height:1.6;">
                                        ❓ <strong>Cần hỗ trợ?</strong> Liên hệ chúng tôi qua email
                                        <a href="mailto:support@bdsplatform.vn" style="color:#2563eb;font-weight:600;">support@bdsplatform.vn</a>
                                        hoặc hotline <strong>1900 xxxx</strong> (8:00 - 22:00 hàng ngày).
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- FOOTER --}}
                <tr>
                    <td style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:24px 40px;text-align:center;">
                        <p style="margin:0 0 6px;color:#9ca3af;font-size:13px;">© {{ date('Y') }} Hệ Thống Bất Động Sản KLTN T6. All rights reserved.</p>
                        <p style="margin:0;color:#d1d5db;font-size:12px;">Đây là email tự động, vui lòng không trả lời trực tiếp email này.</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
