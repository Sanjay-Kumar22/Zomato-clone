<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\OrderDispute;

class DisputeController extends Controller
{
    public function index()
    {
        $disputes = OrderDispute::with('order', 'user')
            ->whereHas('order.restaurant', fn ($q) => $q->where('user_id', auth()->id()))
            ->latest()
            ->paginate(15);

        return view('owner.disputes', compact('disputes'));
    }
}
