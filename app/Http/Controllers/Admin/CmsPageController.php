<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CmsPageController extends Controller
{
    public function index()
    {
        $pages = CmsPage::orderBy('title')->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $page = CmsPage::create($data);
        $this->audit('cms_page.created', $page);

        return back()->with('success', 'CMS page saved.');
    }

    public function update(Request $request, CmsPage $page)
    {
        $data = $this->validated($request, $page);
        $page->update($data);
        $this->audit('cms_page.updated', $page);

        return back()->with('success', 'CMS page updated.');
    }

    public function destroy(CmsPage $page)
    {
        $this->audit('cms_page.deleted', $page);
        $page->delete();

        return back()->with('success', 'CMS page removed.');
    }

    public function showPublic(string $slug)
    {
        $page = CmsPage::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('customer.cms-page', compact('page'));
    }

    private function validated(Request $request, ?CmsPage $page = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title'))]);

        $data = $request->validate([
            'title' => 'required|string|max:180',
            'slug' => ['required', 'string', 'max:200', Rule::unique('cms_pages', 'slug')->ignore($page?->id)],
            'body' => 'required|string|max:50000',
            'is_published' => 'nullable|boolean',
        ]);
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }

    private function audit(string $action, CmsPage $page): void
    {
        AdminAuditLog::create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'subject_type' => CmsPage::class,
            'subject_id' => (string) $page->id,
            'description' => "{$action} page: {$page->title}.",
            'ip_address' => request()->ip(),
        ]);
    }
}
