<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>E-Tiket Kamu</title>
</head>
<body style="margin:0; padding:0; background-color:#F7F8FC; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F7F8FC; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 480px; background-color:#FFFFFF; border-radius: 16px; overflow: hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#525EA7; padding: 24px 32px;">
                            <span style="color:#FFFFFF; font-size: 20px; font-weight: 800;">TiketIn</span>
                        </td>
                    </tr>

                    <!-- Intro -->
                    <tr>
                        <td style="padding: 32px 32px 8px;">
                            <h1 style="margin:0 0 6px; font-size: 20px; color:#1F2937;">E-Tiket Kamu Sudah Siap</h1>
                            <p style="margin:0 0 2px; font-size: 14px; color:#4B5768;">
                                Atas nama <strong>{{ $order->customer_name }}</strong>
                            </p>
                            <p style="margin:0; font-size: 12px; color:#9CA3AF; font-family: monospace;">
                                Ref: {{ $order->merchant_ref }}
                            </p>
                        </td>
                    </tr>

                    <!-- Satu blok per tiket -->
                    @foreach($order->tickets as $ticket)
                    <tr>
                        <td style="padding: 16px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #E5E7EB; border-radius: 12px; overflow: hidden;">
                                <tr>
                                    <td style="background-color:#525EA7; padding: 16px 20px; color:#FFFFFF;">
                                        <div style="font-size: 11px; opacity: 0.8;">{{ $ticket->ticketCategory->name }}</div>
                                        <div style="font-size: 16px; font-weight: 700; margin-top: 2px;">
                                            {{ $ticket->ticketCategory->event->name }}
                                        </div>
                                        <div style="font-size: 12px; margin-top: 8px; opacity: 0.85;">
                                            {{ \Carbon\Carbon::parse($ticket->ticketCategory->event->event_date)->translatedFormat('l, d F Y · H:i') }} WIB
                                        </div>
                                        <div style="font-size: 12px; opacity: 0.85;">
                                            {{ $ticket->ticketCategory->event->location }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding: 20px; background-color:#FFFFFF;">
                                        {!! $qrSvgs[$ticket->id] !!}
                                        <p style="margin: 10px 0 0; font-size: 12px; color:#9CA3AF; font-family: monospace;">
                                            {{ $ticket->ticket_code }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach

                    <!-- Catatan -->
                    <tr>
                        <td style="padding: 8px 32px 32px;">
                            <p style="margin:0; font-size: 12px; line-height:1.6; color:#9CA3AF;">
                                Tunjukkan QR code di atas ke panitia saat masuk lokasi acara. Simpan email ini atau
                                screenshot QR-nya sebagai cadangan.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 32px; background-color:#F7F8FC; border-top: 1px solid #E5E7EB;">
                            <p style="margin:0; font-size: 12px; color:#9CA3AF;">&copy; {{ date('Y') }} TiketIn Platform</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
