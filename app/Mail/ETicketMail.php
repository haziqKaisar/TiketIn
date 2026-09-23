<?php

namespace App\Mail;

use App\Models\Order;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ETicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        $eventName = optional(
            optional($this->order->tickets->first())->ticketCategory?->event
        )->name;

        return new Envelope(
            subject: 'E-Tiket Kamu' . ($eventName ? " — {$eventName}" : '') . ' — TiketIn',
        );
    }

    public function content(): Content
    {
        $qrService = app(QrCodeService::class);

        // SVG markup per tiket (bisa lebih dari satu kalau order berisi
        // beberapa tiket sekaligus), di-render langsung inline di blade.
        $qrSvgs = $this->order->tickets->mapWithKeys(function ($ticket) use ($qrService) {
            return [$ticket->id => $qrService->svgMarkup($ticket->ticket_code, 160)];
        });

        return new Content(
            view: 'emails.eticket',
            with: [
                'order'  => $this->order,
                'qrSvgs' => $qrSvgs,
            ],
        );
    }
}
