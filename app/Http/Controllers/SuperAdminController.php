<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function organizationIndex(Request $request)
    {
        $status = $request->query('status', 'pending');

        $organizations = Organization::when(
            $status !== 'all',
            fn ($q) => $q->where('status', $status)
        )->latest()->get();

        return view('superadmin.organizations.index', compact('organizations', 'status'));
    }

    public function organizationApprove(Organization $organization)
    {
        $organization->update(['status' => 'approved']);

        return back()->with('success', "Organisasi \"{$organization->name}\" disetujui.");
    }

    public function organizationReject(Request $request, Organization $organization)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $organization->update([
            'status'          => 'rejected',
            'rejected_reason' => $request->input('reason'),
        ]);

        // Hapus akun admin yang terhubung ke organisasi ini. Organisasinya
        // sendiri TETAP disimpan (buat riwayat/audit, status = rejected),
        // tapi akun login-nya dihapus supaya emailnya bebas dipakai lagi
        // kalau mereka mau coba daftar ulang.
        $organization->users()->delete();

        return back()->with('success', "Organisasi \"{$organization->name}\" ditolak. Email admin-nya sudah dibebaskan, mereka bisa daftar ulang kalau mau.");
    }

    // =========================================================
    // Pengajuan Penarikan Saldo
    // =========================================================
    public function withdrawalIndex(Request $request)
    {
        $status = $request->query('status', 'pending');

        $withdrawals = WithdrawalRequest::with('organization')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        return view('superadmin.withdrawals.index', compact('withdrawals', 'status'));
    }

    public function withdrawalComplete(WithdrawalRequest $withdrawalRequest)
    {
        if ($withdrawalRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $withdrawalRequest->update([
            'status'       => 'completed',
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Penarikan ditandai selesai ditransfer.');
    }

    public function withdrawalReject(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        if ($withdrawalRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $withdrawalRequest->update([
            'status'       => 'rejected',
            'admin_note'   => $request->input('reason'),
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan penarikan ditolak.');
    }
}
