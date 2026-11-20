<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewReport;
use Illuminate\Http\Request;

class ReviewModerationController extends Controller
{
    public function index(Request $request)
    {
        $query = ReviewReport::with('review.user', 'review.restaurant', 'reporter', 'moderator');
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $reports = $query->latest()->paginate(15)->withQueryString();

        return view('admin.review-reports.index', compact('reports'));
    }

    public function update(Request $request, ReviewReport $report)
    {
        $request->validate(['action' => 'required|in:hide,keep,close']);

        if ($report->status !== 'open') {
            return back()->with('error', 'This report has already been moderated.');
        }

        if ($request->action === 'hide') {
            $report->review->update(['moderation_status' => 'hidden']);
        } elseif ($request->action === 'keep') {
            $report->review->update(['moderation_status' => 'visible']);
        }

        $report->update([
            'status' => $request->action === 'close' ? 'closed' : 'reviewed',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'Review report moderated.');
    }
}
