<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlatformSettings
{
    public function value(string $key): float
    {
        $fallback = (float) config('food-platform.' . $key, 0);
        if (!Schema::hasTable('platform_settings')) {
            return $fallback;
        }

        $value = Cache::remember('food-platform-setting:' . $key, now()->addMinute(), function () use ($key) {
            return DB::table('platform_settings')->where('key', $key)->value('value');
        });

        return $value === null ? $fallback : (float) $value;
    }

    public function forget(string $key): void
    {
        Cache::forget('food-platform-setting:' . $key);
    }
}
