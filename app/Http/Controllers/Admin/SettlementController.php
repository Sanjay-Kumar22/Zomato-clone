<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\RestaurantSettlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => ['nullable', Rule::in(['pending', 'approved', 'paid', 'rejected'])]]);

        $settlements = RestaurantSettlement::with('restaurant.owner', 'requester', 'reviewer')
            ->withCount('orders')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.settlements.index', compact('settlements'));
    }

    public function update(Request $request, RestaurantSettlement $settlement)
    {
        $request->validate([
            'status' => ['required', Rule::in(['approved', 'paid', 'rejected'])],
            'payout_reference' => 'required_if:status,paid|nullable|string|max:120',
            'review_note' => 'nullable|string|max:1000',
        ]);

        $updated = DB::transaction(function () use ($request, $settlement): bool {
            $locked = RestaurantSettlement::whereKey($settlement->id)->lockForUpdate()->firstOrFail();
            $nextStatus = $request->string('status')->toString();

            $allowed = match ($locked->status) {
                'pending' => ['approved', 'rejected'],
                'approved' => ['paid', 'rejected'],
                default => [],
            };

            if (!in_array($nextStatus, $allowed, true)) {
                return false;
            }

            $locked->update([
                'status' => $nextStatus,
                'reviewed_by' => auth()->id(),
                'review_note' => $request->input('review_note'),
                'payout_reference' => $nextStatus === 'paid' ? $request->input('payout_reference') : null,
                'reviewed_at' => now(),
                'paid_at' => $nextStatus === 'paid' ? now() : null,
            ]);
            AdminAuditLog::create([
                'actor_id' => auth()->id(),
                'action' => 'settlement.' . $nextStatus,
                'subject_type' => RestaurantSettlement::class,
                'subject_id' => (string) $locked->id,
                'description' => "Changed payout request #{$locked->id} to {$nextStatus}.",
                'ip_address' => request()->ip(),
                'metadata' => [
                    'status' => $nextStatus,
                    'payout_reference' => $nextStatus === 'paid' ? $request->input('payout_reference') : null,
                    'review_note' => $request->input('review_note'),
                ],
            ]);

            return true;
        });

        if (!$updated) {
            return back()->with('error', 'That settlement status transition is not allowed.');
        }

        return back()->with('success', 'Settlement status updated.');
    }
}
