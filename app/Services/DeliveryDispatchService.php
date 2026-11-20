<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderReadyForPickup;
use Illuminate\Support\Facades\Notification;

class DeliveryDispatchService
{
    public function notifyAvailableAgents(Order $order, array $exceptAgentIds = []): int
    {
        $agents = User::query()
            ->where('role', 'delivery_agent')
            ->where('is_online', true)
            ->where('kyc_status', 'approved')
            ->whereNotIn('id', $exceptAgentIds)
            ->get();

        if ($agents->isEmpty()) {
            return 0;
        }

        $order->loadMissing('restaurant', 'address');
        Notification::send($agents, new OrderReadyForPickup($order));

        return $agents->count();
    }
}
