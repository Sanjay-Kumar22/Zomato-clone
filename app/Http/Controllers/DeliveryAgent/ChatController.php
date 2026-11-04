<?php

namespace App\Http\Controllers\DeliveryAgent;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function show(Order $order)
    {
        abort_if($order->delivery_agent_id !== auth()->id(), 403);

        $order->load('user');
        $messages = OrderMessage::where('order_id', $order->id)->orderBy('created_at')->get();

        OrderMessage::where('order_id', $order->id)
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('delivery.chat', compact('order', 'messages'));
    }

    public function send(Request $request, Order $order)
    {
        abort_if($order->delivery_agent_id !== auth()->id(), 403);

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

    public function poll(Request $request, Order $order)
    {
        abort_if($order->delivery_agent_id !== auth()->id(), 403);

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

    // Unread counts for ALL of this agent's active orders in one call —
    // used by the dashboard to badge each order's chat icon and fire a
    // browser notification, without needing to open any individual chat.
    public function unreadCounts()
    {
        $counts = OrderMessage::whereIn('order_id', function ($q) {
                $q->select('id')->from('orders')
                    ->where('delivery_agent_id', auth()->id())
                    ->where('status', 'out_for_delivery');
            })
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->selectRaw('order_id, count(*) as unread')
            ->groupBy('order_id')
            ->pluck('unread', 'order_id');

        return response()->json(['counts' => $counts]);
    }
}
