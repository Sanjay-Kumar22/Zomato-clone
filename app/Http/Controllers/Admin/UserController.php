<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:customer,restaurant_owner,delivery_agent,admin',
        ]);

        // Safety: prevent an admin from demoting themselves and losing access.
        if ($user->id === auth()->id() && $request->role !== 'admin') {
            return back()->with('error', "You can't change your own admin role.");
        }

        $user->update(['role' => $request->role]);

        return back()->with('success', $user->name . "'s role updated to " . str_replace('_', ' ', $request->role) . '.');
    }

    public function updateKyc(Request $request, User $user)
    {
        abort_if($user->role !== 'delivery_agent', 404);
        $request->validate(['kyc_status' => 'required|in:approved,rejected']);

        if ($request->kyc_status === 'approved' && !$user->kyc_document) {
            return back()->with('error', 'This agent has not submitted a KYC document.');
        }

        $user->update(['kyc_status' => $request->kyc_status]);

        return back()->with('success', 'Delivery partner verification updated.');
    }

    public function kycDocument(User $user)
    {
        abort_if($user->role !== 'delivery_agent' || !$user->kyc_document, 404);
        abort_unless(Storage::disk('local')->exists($user->kyc_document), 404);

        return Storage::disk('local')->download($user->kyc_document);
    }
}