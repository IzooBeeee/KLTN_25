{{-- resources/views/emails/contact_broker.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #10b981; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .info { background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .content { background: #fff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; white-space: pre-wrap; }
        .footer { margin-top: 30px; text-align: center; color: #6b7280; font-size: 12px; }
        .btn-reply { display: inline-block; margin-top: 15px; padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📩 Liên hệ mới từ khách hàng</h2>
        </div>
        
        <div class="info">
            <p><strong>Khách hàng:</strong> {{ $ten_khach }}</p>
            <p><strong>Email:</strong> {{ $email_khach }}</p>
            <p><strong>Gửi đến:</strong> {{ $ten_moi_gioi }}</p>
        </div>
        
        <div class="content">
            <h3>Nội dung:</h3>
            <p>{{ $noi_dung }}</p>
        </div>
        
        <div style="text-align: center;">
            <a href="mailto:{{ $email_khach }}" class="btn-reply">✉️ Trả lời ngay</a>
        </div>
        
        <div class="footer">
            <p>Email từ hệ thống BrokerHub</p>
        </div>
    </div>
</body>
</html>