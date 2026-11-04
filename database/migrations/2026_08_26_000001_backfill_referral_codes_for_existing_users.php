<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Users created before the referral feature shipped never got a code
        // (it only auto-generates on the `creating` event for brand-new accounts).
        User::whereNull('referral_code')->each(function (User $user) {
            do {
                $code = strtoupper(Str::random(6));
            } while (User::where('referral_code', $code)->exists());

            $user->update(['referral_code' => $code]);
        });
    }

    public function down(): void
    {
        // Nothing to revert — leaving codes in place is harmless.
    }
};
