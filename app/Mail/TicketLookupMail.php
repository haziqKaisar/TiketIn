<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketLookupMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $email;
    public string $signedUrl;

    public function __construct(string $email, string $signedUrl)
    {
        $this->email = $email;
        $this->signedUrl = $signedUrl;
    }

    public function build()
    {
        return $this->subject('Tautan Cek Tiket Kamu — TiketIn')
            ->view('emails.ticket_lookup');
    }
}
