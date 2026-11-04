<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderReadyForPickup extends Notification
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
            'order_id'   => $this->order->id,
            'restaurant' => $this->order->restaurant->name ?? 'Restaurant',
            'pincode'    => $this->order->address->pincode ?? '',
            'total'      => $this->order->total,
            'message'    => 'Order #' . $this->order->id . ' from ' . ($this->order->restaurant->name ?? 'a restaurant') . ' is ready for pickup!',
        ];
    }
}
