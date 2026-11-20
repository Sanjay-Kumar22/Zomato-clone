<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\AdminAuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->latest()->get();

        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'required|image|max:5120',
            'url' => ['nullable', 'string', 'max:2048', $this->destinationRule()],
            'sort_order' => 'required|integer|min:0|max:65535',
            'is_active' => 'nullable|boolean',
        ]);
        $data['image'] = $request->file('image')->store('banners', 'public');
        $data['is_active'] = $request->boolean('is_active');

        $banner = Banner::create($data);
        AdminAuditLog::create([
            'actor_id' => auth()->id(),
            'action' => 'banner.created',
            'subject_type' => Banner::class,
            'subject_id' => (string) $banner->id,
            'description' => "Created homepage banner {$banner->title}.",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Banner published to the customer homepage.');
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:5120',
            'url' => ['nullable', 'string', 'max:2048', $this->destinationRule()],
            'sort_order' => 'required|integer|min:0|max:65535',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image);
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);
        AdminAuditLog::create([
            'actor_id' => auth()->id(),
            'action' => 'banner.updated',
            'subject_type' => Banner::class,
            'subject_id' => (string) $banner->id,
            'description' => "Updated homepage banner {$banner->title}.",
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner)
    {
        AdminAuditLog::create([
            'actor_id' => auth()->id(),
            'action' => 'banner.deleted',
            'subject_type' => Banner::class,
            'subject_id' => (string) $banner->id,
            'description' => "Deleted homepage banner {$banner->title}.",
            'ip_address' => request()->ip(),
        ]);
        Storage::disk('public')->delete($banner->image);
        $banner->delete();

        return back()->with('success', 'Banner removed.');
    }

    private function destinationRule(): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null || trim($value) === '') {
                return;
            }

            if (str_starts_with($value, '/') && !str_starts_with($value, '//') && !str_contains($value, '\\')) {
                return;
            }

            $parts = parse_url($value);
            if (
                filter_var($value, FILTER_VALIDATE_URL)
                && is_array($parts)
                && isset($parts['scheme'], $parts['host'])
                && in_array(strtolower($parts['scheme']), ['http', 'https'], true)
            ) {
                return;
            }

            $fail('The destination must be a site path beginning with / or a full http(s) URL.');
        };
    }
}
