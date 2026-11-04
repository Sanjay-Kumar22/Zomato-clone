<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderDispute;
use App\Services\WalletService;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderDispute::with('order.restaurant', 'user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $disputes = $query->latest()->paginate(15)->withQueryString();

        return view('admin.disputes.index', compact('disputes'));
    }

    public function resolve(Request $request, OrderDispute $dispute, WalletService $wallet)
    {
        if ($dispute->status !== 'open') {
            return back()->with('error', 'This dispute has already been handled.');
        }

        $request->validate([
            'action'          => 'required|in:refund,reject',
            'refund_amount'   => 'required_if:action,refund|nullable|numeric|min:0.01',
            'resolution_note' => 'nullable|string|max:1000',
        ]);

        if ($request->action === 'refund') {
            $wallet->credit($dispute->user, (float) $request->refund_amount, 'refund', $dispute->order_id);

            $dispute->update([
                'status'           => 'resolved',
                'refund_amount'    => $request->refund_amount,
                'resolution_note'  => $request->resolution_note,
                'resolved_by'      => auth()->id(),
                'resolved_at'      => now(),
            ]);

            return back()->with('success', 'Refund of ₹' . number_format($request->refund_amount, 2) . ' credited to the customer\'s wallet.');
        }

        $dispute->update([
            'status'          => 'rejected',
            'resolution_note' => $request->resolution_note,
            'resolved_by'     => auth()->id(),
            'resolved_at'     => now(),
        ]);

        return back()->with('success', 'Dispute rejected.');
    }
}
