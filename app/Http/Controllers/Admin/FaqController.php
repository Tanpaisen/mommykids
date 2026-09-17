<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::query();

        if ($request->filled('search')) {
            $query->where('question', 'like', '%' . $request->search . '%');
        }

        $faqs = $query->orderBy('sort_order', 'asc')->latest()->paginate(10);
        return view('admin.hoi-dap.index', compact('faqs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required',
            'answer' => 'required',
        ]);

        Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'category' => $request->category ?? 'Chung',
            'is_active' => $request->has('is_active') ? 1 : 0,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->back()->with('success', 'Thêm câu hỏi thành công!');
    }

    public function destroy($id)
    {
        Faq::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Đã xóa câu hỏi!');
    }
}