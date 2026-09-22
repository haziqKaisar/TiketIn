<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cek Tiket Kamu</title>
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

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px;">
                            <h1 style="margin:0 0 12px; font-size: 20px; color:#1F2937;">Ini link untuk cek tiketmu</h1>
                            <p style="margin:0 0 20px; font-size: 14px; line-height: 1.6; color:#4B5768;">
                                Kami menerima permintaan untuk melihat pesanan tiket yang terhubung dengan email
                                <strong>{{ $email }}</strong>. Klik tombol di bawah untuk melihat status dan e-tiketmu.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="border-radius: 8px; background-color:#FFC349;">
                                        <a href="{{ $signedUrl }}"
                                           style="display:inline-block; padding: 12px 28px; font-size: 14px; font-weight: 700; color:#111827; text-decoration:none;">
                                            Lihat Tiket Saya
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 24px 0 0; font-size: 12px; line-height: 1.6; color:#9CA3AF;">
                                Link ini hanya berlaku 30 menit dan hanya berfungsi lewat email ini. Kalau kamu tidak
                                merasa meminta ini, abaikan saja — tidak ada yang berubah pada pesananmu.
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
