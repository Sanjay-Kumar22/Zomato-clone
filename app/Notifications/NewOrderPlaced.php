<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderPlaced extends Notification
{
    use Queueable;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id'     => $this->order->id,
            'customer'     => $this->order->user->name ?? 'A customer',
            'restaurant'   => $this->order->restaurant->name ?? '',
            'total'        => $this->order->total,
            'items_count'  => $this->order->items()->count(),
            'message'      => 'New order #' . $this->order->id . ' received — ₹' . number_format($this->order->total, 2),
        ];
    }
}
