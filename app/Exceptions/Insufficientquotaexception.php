<?php

namespace App\Exceptions;

use Exception;

/**
 * Dilempar saat kuota tiket tidak cukup pada saat pengecekan TERKUNCI
 * (lockForUpdate) di dalam transaction checkout — beda dari exception
 * umum lain, supaya CheckoutController bisa kasih pesan yang tepat
 * ke user tanpa nge-log sebagai error sungguhan.
 */
class InsufficientQuotaException extends Exception
{
}
