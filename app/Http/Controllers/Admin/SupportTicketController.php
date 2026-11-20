<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => ['nullable', Rule::in(['open', 'in_progress', 'resolved'])]]);
        $tickets = SupportTicket::with('user', 'order')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.support.index', compact('tickets'));
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved'])],
            'admin_reply' => 'nullable|string|max:3000',
        ]);
        if ($data['status'] === 'resolved' && trim((string) ($data['admin_reply'] ?? $ticket->admin_reply)) === '') {
            return back()->withInput()->withErrors(['admin_reply' => 'Add a reply before resolving the ticket.']);
        }

        $ticket->update([
            'status' => $data['status'],
            'admin_reply' => $data['admin_reply'] ?? $ticket->admin_reply,
            'responded_by' => $data['admin_reply'] ? auth()->id() : $ticket->responded_by,
            'responded_at' => $data['admin_reply'] ? now() : $ticket->responded_at,
        ]);
        AdminAuditLog::create([
            'actor_id' => auth()->id(),
            'action' => 'support_ticket.updated',
            'subject_type' => SupportTicket::class,
            'subject_id' => (string) $ticket->id,
            'description' => "Updated support ticket #{$ticket->id} to {$ticket->status}.",
            'ip_address' => $request->ip(),
            'metadata' => ['status' => $ticket->status],
        ]);

        return back()->with('success', 'Support ticket updated.');
    }
}
