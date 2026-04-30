<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã xác nhận quên mật khẩu</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                {{-- HEADER --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);padding:36px 40px;text-align:center;">
                        <div style="display:inline-block;background:rgba(255,255,255,0.15);border-radius:50%;width:60px;height:60px;line-height:60px;font-size:28px;margin-bottom:16px;">🔐</div>
                        <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;letter-spacing:-0.5px;">Đặt lại mật khẩu</h1>
                        <p style="margin:8px 0 0;color:rgba(255,255,255,0.85);font-size:14px;">Hệ Thống Bất Động Sản KLTN T6</p>
                    </td>
                </tr>

                {{-- BODY --}}
                <tr>
                    <td style="padding:40px 40px 24px;">
                        <p style="margin:0 0 8px;color:#374151;font-size:16px;">Xin chào,</p>
                        <p style="margin:0 0 28px;color:#6b7280;font-size:15px;line-height:1.6;">
                            Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.
                            Sử dụng mã OTP dưới đây để tiếp tục:
                        </p>

                        {{-- OTP BOX --}}
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center" style="padding:8px 0 32px;">
                                    <div style="background:#f0fdf4;border:2px dashed #10b981;border-radius:12px;padding:28px 40px;display:inline-block;">
                                        <p style="margin:0 0 8px;color:#6b7280;font-size:13px;text-transform:uppercase;letter-spacing:2px;font-weight:600;">Mã xác nhận OTP</p>
                                        <p style="margin:0;color:#065f46;font-size:42px;font-weight:800;letter-spacing:12px;font-family:'Courier New',monospace;">{{ $code }}</p>
                                        <p style="margin:12px 0 0;color:#10b981;font-size:13px;font-weight:600;">⏱ Hiệu lực trong <strong>5 phút</strong></p>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        {{-- STEPS --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;border-radius:10px;margin-bottom:28px;">
                            <tr>
                                <td style="padding:20px 24px;">
                                    <p style="margin:0 0 12px;color:#374151;font-size:14px;font-weight:700;">Hướng dẫn sử dụng:</p>
                                    <table cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding:4px 0;color:#6b7280;font-size:14px;">
                                                <span style="display:inline-block;background:#10b981;color:white;border-radius:50%;width:20px;height:20px;line-height:20px;text-align:center;font-size:11px;font-weight:bold;margin-right:8px;">1</span>
                                                Quay lại trang xác thực mã OTP
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:4px 0;color:#6b7280;font-size:14px;">
                                                <span style="display:inline-block;background:#10b981;color:white;border-radius:50%;width:20px;height:20px;line-height:20px;text-align:center;font-size:11px;font-weight:bold;margin-right:8px;">2</span>
                                                Nhập mã <strong style="color:#065f46;">{{ $code }}</strong> vào ô xác nhận
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:4px 0;color:#6b7280;font-size:14px;">
                                                <span style="display:inline-block;background:#10b981;color:white;border-radius:50%;width:20px;height:20px;line-height:20px;text-align:center;font-size:11px;font-weight:bold;margin-right:8px;">3</span>
                                                Đặt mật khẩu mới cho tài khoản
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {{-- SECURITY WARNING --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#fef3c7;border-left:4px solid #f59e0b;border-radius:0 8px 8px 0;margin-bottom:20px;">
                            <tr>
                                <td style="padding:14px 18px;">
                                    <p style="margin:0;color:#92400e;font-size:13px;line-height:1.6;">
                                        ⚠️ <strong>Lưu ý bảo mật:</strong> Nếu bạn không yêu cầu đặt lại mật khẩu,
                                        vui lòng bỏ qua email này. Mã OTP sẽ tự hết hạn và tài khoản của bạn vẫn an toàn.
                                        Không chia sẻ mã này với bất kỳ ai.
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
                        <p style="margin:0;color:#d1d5db;font-size:12px;">Đây là email tự động, vui lòng không trả lời trực tiếp.</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>