<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrganizationProfileController extends Controller
{
    public function edit()
    {
        $organization = Auth::user()->organization;

        abort_if(! $organization, 404, 'Akun kamu tidak terhubung ke organisasi manapun.');

        return view('admin.organization.edit', compact('organization'));
    }

    public function update(Request $request)
    {
        $organization = Auth::user()->organization;

        abort_if(! $organization, 404, 'Akun kamu tidak terhubung ke organisasi manapun.');

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email',
            'phone'       => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'logo'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }
            $data['logo'] = $request->file('logo')->store('org-logos', 'public');
        }

        $organization->update($data);

        return back()->with('success', 'Profil organisasi berhasil diperbarui.');
    }
}
