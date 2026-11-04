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
        ]);

        $user = auth()->user();
        $data = $request->only(['name', 'phone']);

        if ($request->hasFile('photo')) {
            if ($user->profile_photo && !str_starts_with($user->profile_photo, 'http')) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = $request->file('photo')->store('profile-photos', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Profile updated!');
    }
}
