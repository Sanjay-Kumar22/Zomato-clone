<?php

namespace App\Http\Controllers\DeliveryAgent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('delivery.profile', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_registration' => 'nullable|string|max:30',
            'kyc_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = auth()->user();
        $data = $request->only(['name', 'phone', 'vehicle_type', 'vehicle_registration']);

        if ($request->hasFile('photo')) {
            if ($user->profile_photo && !str_starts_with($user->profile_photo, 'http')) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = $request->file('photo')->store('profile-photos', 'public');
        }

        if ($request->hasFile('kyc_document')) {
            $data['kyc_document'] = $request->file('kyc_document')->store('delivery-kyc', 'local');
            $data['kyc_status'] = 'pending';
        }

        $user->update($data);

        return back()->with('success', 'Profile updated!');
    }
}
