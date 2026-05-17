<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ mới từ khách hàng</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                {{-- HEADER --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);padding:36px 40px;text-align:center;">
                        <div style="display:inline-block;background:rgba(255,255,255,0.15);border-radius:50%;width:60px;height:60px;line-height:60px;font-size:28px;margin-bottom:16px;">📩</div>
                        <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;">Bạn có tin nhắn mới!</h1>
                        <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;">Hệ Thống Bất Động Sản KLTN T6</p>
                    </td>
                </tr>

                {{-- GREETING --}}
                <tr>
                    <td style="padding:32px 40px 0;">
                        <p style="margin:0 0 6px;color:#374151;font-size:16px;">Xin chào <strong>{{ $ten_moi_gioi }}</strong>,</p>
                        <p style="margin:0 0 24px;color:#6b7280;font-size:15px;line-height:1.6;">
                            Một khách hàng vừa gửi liên hệ đến bạn qua nền tảng. Dưới đây là thông tin chi tiết:
                        </p>
                    </td>
                </tr>

                {{-- SENDER INFO --}}
                <tr>
                    <td style="padding:0 40px 24px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;overflow:hidden;">
                            <tr>
                                <td style="background:#065f46;padding:12px 20px;">
                                    <p style="margin:0;color:#ffffff;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Thông tin người gửi</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 20px;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding:6px 0;border-bottom:1px solid #d1fae5;">
                                                <span style="color:#6b7280;font-size:13px;display:inline-block;width:110px;">👤 Họ tên:</span>
                                                <strong style="color:#111827;font-size:14px;">{{ $ten_khach }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px 0;border-bottom:1px solid #d1fae5;">
                                                <span style="color:#6b7280;font-size:13px;display:inline-block;width:110px;">✉️ Email:</span>
                                                <strong style="color:#111827;font-size:14px;">{{ $email_khach }}</strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px 0;">
                                                <span style="color:#6b7280;font-size:13px;display:inline-block;width:110px;">📌 Tiêu đề:</span>
                                                <strong style="color:#065f46;font-size:14px;">{{ $tieu_de }}</strong>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- MESSAGE CONTENT --}}
                <tr>
                    <td style="padding:0 40px 28px;">
                        <p style="margin:0 0 10px;color:#374151;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Nội dung tin nhắn:</p>
                        <div style="background:#f9fafb;border-left:4px solid #10b981;border-radius:0 8px 8px 0;padding:20px 22px;">
                            <p style="margin:0;color:#374151;font-size:15px;line-height:1.8;white-space:pre-wrap;">{{ $noi_dung }}</p>
                        </div>
                    </td>
                </tr>

                {{-- CTA --}}
                <tr>
                    <td style="padding:0 40px 36px;text-align:center;">
                        <a href="mailto:{{ $email_khach }}?subject=Re: {{ $tieu_de }}"
                           style="display:inline-block;padding:14px 36px;background:linear-gradient(135deg,#10b981,#059669);color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:700;letter-spacing:0.3px;">
                            ✉️ Trả lời ngay
                        </a>
                        <p style="margin:12px 0 0;color:#9ca3af;font-size:12px;">hoặc reply trực tiếp vào email này</p>
                    </td>
                </tr>

                {{-- DIVIDER --}}
                <tr>
                    <td style="padding:0 40px;">
                        <div style="height:1px;background:#e5e7eb;"></div>
                    </td>
                </tr>

                {{-- NOTICE --}}
                <tr>
                    <td style="padding:20px 40px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#fef3c7;border-left:4px solid #f59e0b;border-radius:0 8px 8px 0;">
                            <tr>
                                <td style="padding:12px 16px;">
                                    <p style="margin:0;color:#92400e;font-size:13px;line-height:1.6;">
                                        💡 <strong>Lưu ý:</strong> Phản hồi sớm sẽ tăng tỷ lệ chốt giao dịch thành công.
                                        Khách hàng thường chờ không quá <strong>30 phút</strong> trước khi liên hệ môi giới khác.
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
                        <p style="margin:0;color:#d1d5db;font-size:12px;">Đây là email tự động từ hệ thống. Vui lòng không trả lời trực tiếp email này.</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>