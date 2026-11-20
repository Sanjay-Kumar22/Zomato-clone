<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderDispute;
use App\Services\RefundService;
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

    public function resolve(Request $request, OrderDispute $dispute, RefundService $refunds)
    {
        if ($dispute->status !== 'open') {
            return back()->with('error', 'This dispute has already been handled.');
        }

        $request->validate([
            'action'          => 'required|in:refund,reject',
            'refund_amount'   => 'required_if:action,refund|nullable|numeric|min:0.01',
            'refund_method'   => 'required_if:action,refund|nullable|in:wallet,original',
            'resolution_note' => 'nullable|string|max:1000',
        ]);

        if ($request->action === 'refund') {
            try {
                $refund = $refunds->issue(
                    $dispute->order,
                    (float) $request->refund_amount,
                    $request->refund_method,
                    $dispute,
                    auth()->user(),
                );
            } catch (\InvalidArgumentException|\RuntimeException|\Illuminate\Http\Client\ConnectionException $exception) {
                return back()->withInput()->with('error', $exception->getMessage());
            }

            if ($refund->status !== 'processed') {
                return back()->withInput()->with('error', $refund->failure_reason ?? 'Refund is pending provider confirmation.');
            }

            $dispute->update([
                'status'           => 'resolved',
                'refund_amount'    => $request->refund_amount,
                'resolution_note'  => trim(($request->resolution_note ?? '') . "\nRefund method: " . $refund->method),
                'resolved_by'      => auth()->id(),
                'resolved_at'      => now(),
            ]);

            return back()->with('success', 'Refund of ₹' . number_format($request->refund_amount, 2) . ' processed to ' . $refund->method . '.');
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
