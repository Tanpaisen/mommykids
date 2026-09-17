<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\HandbookCategory;

class HandbookController extends Controller
{
    // Trang đọc cẩm nang dạng Sách / Thư viện bài viết
    public function index($slug = null)
    {
        // 1. Lấy toàn bộ cây danh mục (Chương -> Mục con -> Bài viết)
        $chapters = HandbookCategory::with(['children.articles', 'articles'])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        // 2. Lấy bài viết đang đọc (nếu truyền slug, ngược lại lấy bài đầu tiên)
        $currentArticle = null;
        if ($slug) {
            $currentArticle = Article::where('slug', $slug)->where('status', 'published')->firstOrFail();
            $currentArticle->increment('views');
        } else {
            // Lấy bài viết đầu tiên của Chương 1
            $firstChapter = $chapters->first();
            if ($firstChapter) {
                $firstCategory = $firstChapter->children->first() ?? $firstChapter;
                $currentArticle = $firstCategory->articles->first();
            }
        }

        // 3. Lấy danh sách tất cả ID bài viết để làm nút "Bài trước" / "Bài tiếp theo"
        $allArticles = Article::where('status', 'published')->orderBy('sort_order')->get();
        $prevArticle = null;
        $nextArticle = null;

        if ($currentArticle) {
            $currentIndex = $allArticles->search(fn($item) => $item->id === $currentArticle->id);
            if ($currentIndex !== false) {
                $prevArticle = $allArticles->get($currentIndex - 1);
                $nextArticle = $allArticles->get($currentIndex + 1);
            }
        }

        return view('client.handbook.index', compact('chapters', 'currentArticle', 'prevArticle', 'nextArticle'));
    }
}