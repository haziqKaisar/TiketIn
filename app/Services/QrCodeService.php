<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Hasilkan QR code sebagai markup SVG (string).
     *
     * SENGAJA pakai SVG (bukan PNG lewat Imagick/GD) — ini format yang
     * SUDAH TERBUKTI jalan di project ini (dipakai juga di halaman e-tiket
     * publik), tidak butuh extension PHP tambahan apa pun, dan tidak
     * tergantung versi bacon/bacon-qr-code yang ter-install.
     */
    public function svgMarkup(string $text, int $size = 200): string
    {
        return QrCode::size($size)->generate($text);
    }
}
