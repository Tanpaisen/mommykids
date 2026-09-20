<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Danh sách đánh giá
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View
    {
        $query = ProductReview::query()
            ->with([
                'user',
                'product',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Tìm kiếm
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($query) use ($search) {
                $query
                    ->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc số sao
        |--------------------------------------------------------------------------
        */
        if ($request->filled('rating')) {
            $rating = (int) $request->input('rating');

            if (in_array($rating, [1, 2, 3, 4, 5], true)) {
                $query->where('rating', $rating);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Lọc thời gian
        |--------------------------------------------------------------------------
        */
        $period = (string) $request->input('period');

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        }

        if ($period === '7days') {
            $query->where(
                'created_at',
                '>=',
                now()->subDays(6)->startOfDay()
            );
        }

        if ($period === '30days') {
            $query->where(
                'created_at',
                '>=',
                now()->subDays(29)->startOfDay()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Danh sách + phân trang
        |--------------------------------------------------------------------------
        */
        $reviews = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Thống kê toàn hệ thống
        |--------------------------------------------------------------------------
        */
        $reviewCount = ProductReview::query()->count();

        $averageRating = round(
            (float) ProductReview::query()->avg('rating'),
            1
        );

        $positiveRatingCount = ProductReview::query()
            ->whereIn('rating', [4, 5])
            ->count();

        $lowRatingCount = ProductReview::query()
            ->whereIn('rating', [1, 2])
            ->count();

        $positiveRatingPercent = $reviewCount > 0
            ? (int) round(
                ($positiveRatingCount / $reviewCount) * 100
            )
            : 0;

        $lowRatingPercent = $reviewCount > 0
            ? (int) round(
                ($lowRatingCount / $reviewCount) * 100
            )
            : 0;

        return view(
            'admin.reviews.index',
            compact(
                'reviews',
                'reviewCount',
                'averageRating',
                'positiveRatingCount',
                'positiveRatingPercent',
                'lowRatingCount',
                'lowRatingPercent'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa đánh giá
    |--------------------------------------------------------------------------
    */
        public function destroy(
        ProductReview $review
    ): RedirectResponse {
        foreach ($review->images ?? [] as $image) {
            if (!empty($image)) {
                Storage::disk('public')->delete($image);
            }
        }

        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with(
                'success',
                'Đã xóa đánh giá thành công.'
            );
    }
}