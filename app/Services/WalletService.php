<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function credit(User $user, float $amount, string $reason, ?int $orderId = null): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $reason, $orderId) {
            $user->increment('wallet_balance', $amount);
            $user->refresh();

            return WalletTransaction::create([
                'user_id'       => $user->id,
                'type'          => 'credit',
                'amount'        => $amount,
                'balance_after' => $user->wallet_balance,
                'reason'        => $reason,
                'order_id'      => $orderId,
            ]);
        });
    }

    // Debits up to the wallet balance — caller should cap the requested amount
    // to $user->wallet_balance beforehand (e.g. at checkout) so this never goes negative.
    public function debit(User $user, float $amount, string $reason, ?int $orderId = null): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $reason, $orderId) {
            $user->decrement('wallet_balance', $amount);
            $user->refresh();

            return WalletTransaction::create([
                'user_id'       => $user->id,
                'type'          => 'debit',
                'amount'        => $amount,
                'balance_after' => $user->wallet_balance,
                'reason'        => $reason,
                'order_id'      => $orderId,
            ]);
        });
    }
}
