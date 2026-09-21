<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductReviewRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProductReviewController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Tạo đánh giá mới
    |--------------------------------------------------------------------------
    */
    public function store(
        StoreProductReviewRequest $request,
        Product $product
    ): RedirectResponse {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Chỉ khách hàng có đơn delivered chứa sản phẩm mới được review
        |--------------------------------------------------------------------------
        */
        $hasPurchasedProduct = Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->exists();

        if (!$hasPurchasedProduct) {
            return back()
                ->withErrors([
                    'review' =>
                        'Bạn chỉ có thể đánh giá sản phẩm sau khi đã mua và nhận hàng thành công.',
                ])
                ->withFragment('product-reviews');
        }

        /*
        |--------------------------------------------------------------------------
        | Mỗi user chỉ có 1 review cho 1 sản phẩm
        |--------------------------------------------------------------------------
        */
        $alreadyReviewed = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyReviewed) {
            return back()
                ->withErrors([
                    'review' =>
                        'Bạn đã đánh giá sản phẩm này rồi.',
                ])
                ->withFragment('product-reviews');
        }

        /*
        |--------------------------------------------------------------------------
        | Upload ảnh review
        |--------------------------------------------------------------------------
        */
        $images = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('reviews', 'public');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Lưu review
        |--------------------------------------------------------------------------
        */
        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => $request->integer('rating'),
            'comment' => $request->input('comment'),
            'images' => $images ?: null,
        ]);

        return back()
            ->with(
                'success',
                'Cảm ơn bạn đã đánh giá sản phẩm.'
            )
            ->withFragment('product-reviews');
    }

    /*
    |--------------------------------------------------------------------------
    | Chỉnh sửa đánh giá
    |--------------------------------------------------------------------------
    */
    public function update(
        StoreProductReviewRequest $request,
        Product $product,
        ProductReview $review
    ): RedirectResponse {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Review phải thuộc đúng user đang đăng nhập
        | và đúng sản phẩm đang xem
        |--------------------------------------------------------------------------
        */
        if (
            (string) $review->user_id !== (string) $user->id ||
            (int) $review->product_id !== (int) $product->id
        ) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Vẫn kiểm tra verified buyer khi update
        |--------------------------------------------------------------------------
        */
        $hasPurchasedProduct = Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->exists();

        if (!$hasPurchasedProduct) {
            return back()
                ->withErrors([
                    'review' =>
                        'Bạn chỉ có thể chỉnh sửa đánh giá của sản phẩm đã mua và nhận hàng thành công.',
                ])
                ->withFragment('product-reviews');
        }

        /*
        |--------------------------------------------------------------------------
        | Giữ ảnh hiện tại nếu user không upload ảnh mới
        |--------------------------------------------------------------------------
        */
        $images = $review->images ?? [];

        /*
        |--------------------------------------------------------------------------
        | Nếu upload ảnh mới:
        | - Xóa ảnh review cũ
        | - Lưu bộ ảnh mới
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('images')) {
            foreach ($images as $oldImage) {
                if ($oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            $images = [];

            foreach ($request->file('images') as $image) {
                $images[] = $image->store('reviews', 'public');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update review cũ - KHÔNG tạo review mới
        |--------------------------------------------------------------------------
        */
        $review->update([
            'rating' => $request->integer('rating'),
            'comment' => $request->input('comment'),
            'images' => $images ?: null,
        ]);

        return back()
            ->with(
                'success',
                'Đánh giá của bạn đã được cập nhật.'
            )
            ->withFragment('product-reviews');
    }
}