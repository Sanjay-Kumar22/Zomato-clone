<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.faqs.index', compact('faqs'));
    }

    public function store(Request $request)
    {
        $faq = Faq::create($this->validated($request));
        $this->audit('faq.created', $faq, "Created FAQ {$faq->question}.");

        return back()->with('success', 'FAQ added to the help center.');
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->validated($request));
        $this->audit('faq.updated', $faq, "Updated FAQ {$faq->question}.");

        return back()->with('success', 'FAQ updated.');
    }

    public function destroy(Faq $faq)
    {
        $this->audit('faq.deleted', $faq, "Deleted FAQ {$faq->question}.");
        $faq->delete();

        return back()->with('success', 'FAQ removed.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category' => 'required|string|max:80',
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:5000',
            'sort_order' => 'required|integer|min:0|max:65535',
            'is_published' => 'nullable|boolean',
        ]);
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }

    private function audit(string $action, Faq $faq, string $description): void
    {
        AdminAuditLog::create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'subject_type' => Faq::class,
            'subject_id' => (string) $faq->id,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
