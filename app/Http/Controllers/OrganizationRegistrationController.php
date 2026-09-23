<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganizationRegistrationController extends Controller
{
    public function create()
    {
        return view('organizations.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'organization_name'  => 'required|string|max:255',
            'organization_email' => 'required|email',
            'organization_phone' => 'nullable|string|max:20',
            'description'        => 'nullable|string|max:1000',
            'admin_name'         => 'required|string|max:255',
            'admin_email'        => 'required|email|unique:users,email',
            'password'           => 'required|string|min:8|confirmed',
        ]);

        $organization = Organization::create([
            'name'        => $data['organization_name'],
            'slug'        => Str::slug($data['organization_name']) . '-' . Str::lower(Str::random(4)),
            'email'       => $data['organization_email'],
            'phone'       => $data['organization_phone'] ?? null,
            'description' => $data['description'] ?? null,
            'status'      => 'pending',
        ]);

        User::create([
            'name'            => $data['admin_name'],
            'email'           => $data['admin_email'],
            'password'        => Hash::make($data['password']),
            'role'            => 'org_admin',
            'organization_id' => $organization->id,
        ]);

        return redirect()->route('organizations.register')->with(
            'success',
            'Pendaftaran berhasil dikirim! Kami akan meninjau organisasimu terlebih dahulu sebelum kamu bisa login.'
        );
    }

    /**
     * Halaman status untuk org_admin yang login tapi organisasinya
     * belum/tidak disetujui.
     */
    public function pending()
    {
        $organization = Auth::user()->organization;

        return view('organizations.pending', compact('organization'));
    }
}
