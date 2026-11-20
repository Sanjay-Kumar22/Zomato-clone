<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    private const DEFAULTS = [
        'gst_percent' => 5,
        'platform_fee_percent' => 2,
        'commission_percent' => 15,
        'membership_monthly_price' => 199,
    ];

    public function index()
    {
        $settings = collect(self::DEFAULTS)->mapWithKeys(fn ($default, $key) => [
            $key => (float) config('food-platform.' . $key, $default),
        ]);
        foreach (DB::table('platform_settings')->whereIn('key', array_keys(self::DEFAULTS))->get() as $setting) {
            $settings[$setting->key] = (float) $setting->value;
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request, \App\Services\PlatformSettings $platformSettings)
    {
        $data = $request->validate([
            'gst_percent' => 'required|numeric|min:0|max:100',
            'platform_fee_percent' => 'required|numeric|min:0|max:100',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'membership_monthly_price' => 'required|numeric|min:0.01|max:100000',
        ]);

        DB::transaction(function () use ($data): void {
            foreach ($data as $key => $value) {
                $existing = DB::table('platform_settings')->where('key', $key)->first();
                $oldValue = $existing?->value;
                DB::table('platform_settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => (string) $value,
                        'updated_by' => auth()->id(),
                        'updated_at' => now(),
                        'created_at' => $existing?->created_at ?? now(),
                    ]
                );
                AdminAuditLog::create([
                    'actor_id' => auth()->id(),
                    'action' => 'platform_setting.updated',
                    'subject_type' => 'platform_setting',
                    'subject_id' => $key,
                    'description' => "Updated {$key} from " . ($oldValue ?? 'default') . " to {$value}.",
                    'ip_address' => request()->ip(),
                    'metadata' => ['old' => $oldValue, 'new' => (string) $value],
                ]);
            }
        });

        foreach (array_keys($data) as $key) {
            $platformSettings->forget($key);
        }

        return back()->with('success', 'Platform settings updated.');
    }

    public function auditLogs()
    {
        $logs = AdminAuditLog::with('actor')->latest()->paginate(30);

        return view('admin.settings.audit-logs', compact('logs'));
    }
}
