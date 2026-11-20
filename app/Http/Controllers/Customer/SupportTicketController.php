<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Faq;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::where('user_id', auth()->id())
            ->with('order')
            ->latest()
            ->paginate(15);
        $orders = Order::where('user_id', auth()->id())->latest()->limit(30)->get(['id', 'status', 'created_at']);
        $faqs = Faq::where('is_published', true)->orderBy('sort_order')->orderBy('id')->get()->groupBy('category');

        return view('customer.support.index', compact('tickets', 'orders', 'faqs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:180',
            'category' => ['required', Rule::in(['order', 'payment', 'delivery', 'account', 'other'])],
            'message' => 'required|string|min:10|max:3000',
            'order_id' => ['nullable', 'integer', Rule::exists('orders', 'id')->where('user_id', auth()->id())],
        ]);
        $data['user_id'] = auth()->id();
        $data['status'] = 'open';
        $ticket = SupportTicket::create($data);

        return redirect()->route('customer.support.index')
            ->with('success', 'Support request #' . $ticket->id . ' submitted.');
    }
}
