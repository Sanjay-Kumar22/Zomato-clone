<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    protected Order $order;
    protected string $status;

    public function __construct(Order $order, string $status)
    {
        $this->order  = $order;
        $this->status = $status;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $messages = [
            'confirmed'         => 'Your order has been confirmed by the restaurant.',
            'preparing'         => 'The restaurant is now preparing your food.',
            'out_for_delivery'  => 'Your order is out for delivery!',
            'delivered'         => 'Your order has been delivered. Enjoy your meal!',
            'cancelled'         => 'Your order has been cancelled.',
        ];

        return [
            'order_id'     => $this->order->id,
            'restaurant'   => $this->order->restaurant->name ?? 'Restaurant',
            'status'       => $this->status,
            'message'      => $messages[$this->status] ?? ('Order status updated to ' . $this->status),
        ];
    }
}
