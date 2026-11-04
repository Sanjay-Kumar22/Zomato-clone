<?php

namespace App\Services;

class LoyaltyService
{
    // Earn rate: 1 point for every ₹20 spent (on subtotal, before delivery/tip)
    protected const EARN_RATE = 20;

    // Redeem rate: 1 point = ₹1 off
    protected const REDEEM_RATE = 1;

    // A customer can only redeem points to cover up to 50% of the item subtotal,
    // so an order never becomes "free" purely off points.
    protected const MAX_REDEEM_PERCENT = 0.5;

    public function pointsForAmount(float $subtotal): int
    {
        return (int) floor($subtotal / self::EARN_RATE);
    }

    public function redeemValue(int $points): float
    {
        return $points * self::REDEEM_RATE;
    }

    // Highest number of points this customer could usefully redeem right now,
    // capped by both their balance and the order's subtotal.
    public function maxRedeemablePoints(float $subtotal, int $userPoints): int
    {
        $capByOrder = (int) floor(($subtotal * self::MAX_REDEEM_PERCENT) / self::REDEEM_RATE);
        return max(0, min($userPoints, $capByOrder));
    }

    // Resolves how many points actually get redeemed for a requested amount,
    // never exceeding what's redeemable.
    public function resolveRedeem(int $requestedPoints, float $subtotal, int $userPoints): int
    {
        $max = $this->maxRedeemablePoints($subtotal, $userPoints);
        return max(0, min($requestedPoints, $max));
    }
}
