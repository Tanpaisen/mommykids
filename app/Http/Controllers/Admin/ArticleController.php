<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $articles = $query->latest()->paginate(10);
        return view('admin.cam-nang.index', compact('articles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        Article::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . time(),
            'thumbnail' => $request->thumbnail ?? 'https://via.placeholder.com/300x200',
            'summary' => $request->summary,
            'content' => $request->content,
            'category' => $request->category ?? 'Giai đoạn của bé',
            'status' => $request->status ?? 'published',
        ]);

        return redirect()->back()->with('success', 'Thêm bài viết cẩm nang thành công!');
    }

    public function destroy($id)
    {
        Article::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Đã xóa bài viết!');
    }
}