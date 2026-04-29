<?php

namespace App\Mail;

use App\Models\GiaoDich;
use App\Models\GoiTin;
use App\Models\MoiGioi;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MoiGioi $moiGioi,
        public GoiTin $goiTin,
        public GiaoDich $giaoDich
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Thanh toán gói tin thành công - BĐS Platform',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment_success',
            with: [
                'moiGioi'  => $this->moiGioi,
                'goiTin'   => $this->goiTin,
                'giaoDich' => $this->giaoDich,
            ]
        );
    }
}
