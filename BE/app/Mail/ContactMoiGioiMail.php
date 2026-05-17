<?php
// app/Mail/ContactMoiGioiMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMoiGioiMail extends Mailable
{
    use Queueable, SerializesModels;

    // ✅ Dùng tên biến rõ ràng, không cần trùng database
    public $ten_khach;          // Tên khách liên hệ
    public $email_khach;        // Email khách liên hệ  
    public $tieu_de;            // Tiêu đề tin nhắn
    public $noi_dung;           // Nội dung tin nhắn
    public $ten_moi_gioi;       // Tên môi giới nhận mail

    public function __construct($ten_khach, $email_khach, $tieu_de, $noi_dung, $ten_moi_gioi)
    {
        $this->ten_khach = $ten_khach;
        $this->email_khach = $email_khach;
        $this->tieu_de = $tieu_de;
        $this->noi_dung = $noi_dung;
        $this->ten_moi_gioi = $ten_moi_gioi;
    }

    public function build()
    {
        return $this->subject('[BrokerHub] ' . $this->tieu_de)
            ->view('emails.contact_moigioi');
    }
}
