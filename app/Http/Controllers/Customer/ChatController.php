<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function show(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);
        abort_if(!$order->delivery_agent_id, 404, 'No delivery partner has been assigned to this order yet.');

        $deliveryAgent = User::find($order->delivery_agent_id);
        $messages = OrderMessage::where('order_id', $order->id)->orderBy('created_at')->get();

        OrderMessage::where('order_id', $order->id)
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('customer.chat', compact('order', 'messages', 'deliveryAgent'));
    }

    public function send(Request $request, Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);
        abort_if(!$order->delivery_agent_id, 404);

        $request->validate(['message' => 'required|string|max:500']);

        $message = OrderMessage::create([
            'order_id'  => $order->id,
            'sender_id' => auth()->id(),
            'message'   => $request->message,
        ]);

        // Return the saved message's real id so the sender's own UI can
        // "upgrade" its optimistic bubble instead of showing a duplicate
        // once the next poll picks the same message back up.
        return response()->json([
            'id'      => $message->id,
            'message' => $message->message,
            'time'    => $message->created_at->format('h:i A'),
        ]);
    }

    // Polled every few seconds by the chat page to fetch new messages
    // without needing a websocket server.
    public function poll(Request $request, Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        $query = OrderMessage::where('order_id', $order->id)->orderBy('created_at');

        if ($request->filled('after_id')) {
            $query->where('id', '>', $request->after_id);
        }

        $messages = $query->get()->map(fn ($m) => [
            'id'      => $m->id,
            'message' => $m->message,
            'is_mine' => $m->sender_id === auth()->id(),
            'time'    => $m->created_at->format('h:i A'),
        ]);

        OrderMessage::where('order_id', $order->id)
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Highest id among MY OWN messages that the other person has read —
        // lets the sender's UI show a "seen" tick without a separate poll.
        $lastSeenId = OrderMessage::where('order_id', $order->id)
            ->where('sender_id', auth()->id())
            ->whereNotNull('read_at')
            ->max('id');

        return response()->json(['messages' => $messages, 'last_seen_id' => $lastSeenId]);
    }

    // Lightweight unread-count check — used by pages OTHER than the chat
    // screen itself (order-detail) to show a badge and fire a browser
    // notification without marking anything as read.
    public function unreadCount(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        $count = OrderMessage::where('order_id', $order->id)
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread' => $count]);
    }
}
