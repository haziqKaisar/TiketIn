<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    private const MIN_WITHDRAWAL = 50000;

    public function index()
    {
        $organization = Auth::user()->organization;
        abort_if(! $organization, 404, 'Akun kamu tidak terhubung ke organisasi manapun.');

        $availableBalance = $organization->availableBalance();
        $requests         = $organization->withdrawalRequests()->latest()->get();
        $hasPending       = $requests->contains('status', 'pending');

        return view('admin.withdrawals.index', compact('organization', 'availableBalance', 'requests', 'hasPending'));
    }

    public function store(Request $request)
    {
        $organization = Auth::user()->organization;
        abort_if(! $organization, 404, 'Akun kamu tidak terhubung ke organisasi manapun.');

        $data = $request->validate([
            'amount'              => 'required|integer|min:' . self::MIN_WITHDRAWAL,
            'bank_name'           => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_name'   => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($organization, $data) {
                // Kunci baris organisasi ini selama pengecekan + pembuatan
                // pengajuan, biar aman kalau tombol submit ke-klik dobel.
                $locked = \App\Models\Organization::where('id', $organization->id)->lockForUpdate()->first();

                if ($locked->withdrawalRequests()->where('status', 'pending')->exists()) {
                    throw new \RuntimeException('Kamu masih punya pengajuan penarikan yang sedang diproses. Tunggu itu selesai dulu sebelum mengajukan lagi.');
                }

                $availableBalance = $locked->availableBalance();

                if ($data['amount'] > $availableBalance) {
                    throw new \RuntimeException('Jumlah penarikan melebihi saldo yang tersedia (Rp ' . number_format($availableBalance, 0, ',', '.') . ').');
                }

                $locked->withdrawalRequests()->create([
                    'amount'              => $data['amount'],
                    'bank_name'           => $data['bank_name'],
                    'bank_account_number' => $data['bank_account_number'],
                    'bank_account_name'   => $data['bank_account_name'],
                    'status'              => 'pending',
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return back()->with('success', 'Pengajuan penarikan berhasil dikirim. Menunggu diproses oleh admin TiketIn.');
    }
}
