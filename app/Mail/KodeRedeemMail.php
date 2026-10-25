<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Email kode redeem — dikirim antrean saat order disetujui (bersama WA). */
class KodeRedeemMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array<int,string> $kodes Daftar "KODE — Judul produk". */
    public function __construct(public Order $order, public array $kodes) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode redeem Rumah Cahaya-bks — Order '.$this->order->kode_order,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.kode-redeem');
    }
}
