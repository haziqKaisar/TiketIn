<?php

namespace App\Http\Controllers;

use App\Models\Organization;
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

        return back()->with('success', "Organisasi \"{$organization->name}\" ditolak.");
    }
}
