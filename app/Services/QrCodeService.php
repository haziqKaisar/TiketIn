<?php

namespace App\Services;

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;

class QrCodeService
{
    /**
     * Hasilkan QR code sebagai PNG binary (raw bytes), untuk dilampirkan
     * atau di-embed ke email.
     *
     * SENGAJA pakai GDLibRenderer, BUKAN ImagickImageBackEnd — GD sudah
     * built-in di PHP (hampir pasti aktif, termasuk di Laragon & kebanyakan
     * hosting produksi), jadi tidak butuh extension/binary tambahan apa pun.
     */
    public function pngBinary(string $text, int $size = 400): string
    {
        $renderer = new GDLibRenderer($size);
        $writer   = new Writer($renderer);

        return $writer->writeString($text);
    }
}
