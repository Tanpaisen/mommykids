<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Cookie dùng để nhận diện giỏ hàng của guest.
     */
    public const COOKIE_NAME = 'cart_uuid';

    /**
     * Thời gian giữ guest cart: 30 ngày.
     */
    public const COOKIE_DAYS = 30;

    public function __construct(
        protected CampaignService $campaignService
    ) {}

    /**
     * ============================================================
     * LẤY HOẶC TẠO GIỎ HÀNG HIỆN TẠI
     * ============================================================
     *
     * User đã đăng nhập:
     *   carts.user_id = Auth::id()
     *
     * Guest:
     *   cookie cart_uuid
     *       ↓
     *   carts.uuid
     */
    public function getCart(): Cart
    {
        /*
         * --------------------------------------------------------
         * USER ĐÃ ĐĂNG NHẬP
         * --------------------------------------------------------
         */
        if (Auth::check()) {
            return Cart::firstOrCreate(
                [
                    'user_id' => Auth::id(),
                    'status' => 'active',
                ],
                [
                    'uuid' => (string) Str::uuid(),
                ]
            );
        }

        /*
         * --------------------------------------------------------
         * GUEST
         * --------------------------------------------------------
         */
        $uuid = Cookie::get(self::COOKIE_NAME);

        if ($uuid) {
            $cart = Cart::query()
                ->where('uuid', $uuid)
                ->where('status', 'active')
                ->whereNull('user_id')
                ->first();

            if ($cart) {
                return $cart;
            }
        }

        /*
         * Chưa có guest cart hoặc cookie cũ không còn hợp lệ
         * → tạo một cart mới.
         */
        $cart = Cart::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => null,
            'status' => 'active',
        ]);

        /*
         * Lưu UUID guest cart trong cookie.
         *
         * Laravel Cookie::queue() nhận thời gian theo phút.
         */
        Cookie::queue(
            self::COOKIE_NAME,
            $cart->uuid,
            60 * 24 * self::COOKIE_DAYS
        );

        return $cart;
    }

    /**
     * ============================================================
     * DANH SÁCH SẢN PHẨM TRONG GIỎ
     * ============================================================
     *
     * Chỉ lấy CartItem có Product còn tồn tại.
     *
     * Product sử dụng SoftDeletes nên sản phẩm bị soft delete
     * sẽ không được tính vào giỏ.
     */
    public function items(): Collection
    {
        $items = $this->getCart()
            ->items()
            ->whereHas('product')
            ->with([
                'product.category',
            ])
            ->get();

        foreach ($items as $item) {
            $product = $item->product;

            if (!$product) {
                continue;
            }

            $campaign = $this->campaignService
                ->getBestCampaignForProduct($product);

            $basePrice = (int) $product->price;

            $effectivePrice = $campaign
                ? (int) $this->campaignService
                    ->getCampaignPrice($campaign, $product)
                : $basePrice;

            /*
             * Đồng bộ giá hiệu lực vào cart_items để giao diện hiện tại
             * (đang đọc $item->price) tự dùng đúng giá Campaign.
             *
             * Khi Campaign hết hạn/tắt, lần đọc giỏ tiếp theo sẽ tự
             * đưa giá về Product.price.
             */
            if ((int) $item->price !== $effectivePrice) {
                $item->price = $effectivePrice;
                $item->save();
            }

            /*
             * Thuộc tính runtime phục vụ UI nếu muốn hiển thị thêm
             * giá gốc / campaign / số tiền giảm.
             * Không ghi các field này xuống database.
             */
            $item->setAttribute('base_price', $basePrice);
            $item->setAttribute('effective_price', $effectivePrice);
            $item->setAttribute('campaign_id', $campaign?->id);
            $item->setAttribute('campaign_type', $campaign?->type);
            $item->setAttribute(
                'campaign_discount_amount',
                max(0, $basePrice - $effectivePrice)
            );
        }

        return $items;
    }

    /**
     * ============================================================
     * TỔNG SỐ LƯỢNG
     * ============================================================
     *
     * Ví dụ:
     * A x2
     * B x3
     *
     * count() = 5
     */
    public function count(): int
    {
        return (int) $this->getCart()
            ->items()
            ->whereHas('product')
            ->sum('quantity');
    }

    /**
     * ============================================================
     * TỔNG TIỀN
     * ============================================================
     *
     * Dùng giá hiệu lực đã được items() đồng bộ:
     * Campaign nếu có, ngược lại là Product.price.
     */
    public function total(): int
    {
        return (int) $this->items()->sum(
            function (CartItem $item) {
                return (int) $item->quantity
                    * (int) $item->price;
            }
        );
    }

    /**
     * ============================================================
     * TỔNG KHỐI LƯỢNG GIỎ HÀNG THEO GRAM
     * ============================================================
     *
     * Dùng để truyền sang GHN.
     *
     * Công thức:
     *
     * product.weight_grams × cart_item.quantity
     *
     * Ví dụ:
     *
     * Sữa:
     * 800g × 2 = 1600g
     *
     * Bỉm:
     * 500g × 1 = 500g
     *
     * Tổng:
     * 2100g
     */
    public function totalWeightGrams(): int
    {
        return (int) $this->items()->sum(
            function (CartItem $item) {
                $weight = (int) (
                    $item->product?->weight_grams ?? 0
                );

                $quantity = (int) $item->quantity;

                return $weight * $quantity;
            }
        );
    }

    /**
     * ============================================================
     * KIỂM TRA SẢN PHẨM THIẾU KHỐI LƯỢNG
     * ============================================================
     *
     * Trả về true nếu có ít nhất một sản phẩm:
     *
     * - weight_grams = null
     * - weight_grams = 0
     * - weight_grams < 0
     *
     * Nên kiểm tra hàm này trước khi gọi API GHN.
     */
    public function hasMissingWeights(): bool
    {
        return $this->items()->contains(
            function (CartItem $item) {
                return (int) (
                    $item->product?->weight_grams ?? 0
                ) <= 0;
            }
        );
    }

    /**
     * ============================================================
     * THÊM SẢN PHẨM VÀO GIỎ
     * ============================================================
     */
    public function add(
        Product $product,
        int $quantity = 1
    ): CartItem {
        /*
         * Không cho thêm Product đã bị soft delete.
         */
        if ($product->trashed()) {
            abort(
                404,
                'Sản phẩm không còn tồn tại.'
            );
        }

        /*
         * Không cho quantity <= 0.
         */
        $quantity = max(1, $quantity);

        $cart = $this->getCart();

        $campaign = $this->campaignService
            ->getBestCampaignForProduct($product);

        $effectivePrice = $campaign
            ? (int) $this->campaignService
                ->getCampaignPrice($campaign, $product)
            : (int) $product->price;

        /*
         * Kiểm tra sản phẩm đã tồn tại trong cart chưa.
         */
        $item = $cart->items()
            ->where(
                'product_id',
                $product->id
            )
            ->first();

        /*
         * Đã có → cộng thêm số lượng.
         */
        if ($item) {
            $item->increment(
                'quantity',
                $quantity
            );

            /*
             * Đồng bộ lại giá hiệu lực hiện tại.
             */
            $item->update([
                'price' => $effectivePrice,
            ]);

            return $item->fresh();
        }

        /*
         * Chưa có → tạo CartItem mới.
         *
         * cart_id sẽ được Eloquent tự gắn
         * thông qua quan hệ $cart->items().
         */
        return $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $effectivePrice,
        ]);
    }

    /**
     * ============================================================
     * CẬP NHẬT SỐ LƯỢNG
     * ============================================================
     */
    public function updateQuantity(
        int $cartItemId,
        int $quantity
    ): void {
        /*
         * Chỉ được cập nhật item nằm trong cart hiện tại.
         */
        $item = $this->getCart()
            ->items()
            ->whereHas('product')
            ->findOrFail($cartItemId);

        /*
         * quantity <= 0 → xóa khỏi giỏ.
         */
        if ($quantity <= 0) {
            $item->delete();

            return;
        }

        $product = $item->product;

        $campaign = $product
            ? $this->campaignService
                ->getBestCampaignForProduct($product)
            : null;

        $effectivePrice = $product
            ? (
                $campaign
                    ? (int) $this->campaignService
                        ->getCampaignPrice($campaign, $product)
                    : (int) $product->price
            )
            : (int) $item->price;

        $item->update([
            'quantity' => $quantity,
            'price' => $effectivePrice,
        ]);
    }

    // ── Xóa item ────────────────────────────────────────────────
    public function remove(string|int $cartItemId): void
    {
        /*
         * Chỉ xóa item thuộc cart hiện tại.
         */
        $this->getCart()
            ->items()
            ->where(
                'id',
                $cartItemId
            )
            ->delete();
    }
/**
 * Xóa toàn bộ sản phẩm trong giỏ hàng hiện tại.
 */
public function clear(): void
{
    $this->getCart()
        ->items()
        ->delete();
}

    /**
     * ============================================================
     * MERGE GUEST CART VÀO USER CART SAU LOGIN
     * ============================================================
     *
     * User hiện tại sử dụng ULID char(26),
     * vì vậy $userId phải hỗ trợ string.
     *
     * Guest:
     *
     * cookie cart_uuid
     *      ↓
     * carts.uuid
     *
     * Sau login:
     *
     * carts.user_id
     */
    public function mergeGuestCart(
        string|int $userId
    ): void {
        $uuid = Cookie::get(
            self::COOKIE_NAME
        );

        if (!$uuid) {
            return;
        }

        /*
         * Tìm guest cart theo UUID trong cookie.
         */
        $guestCart = Cart::query()
            ->where('uuid', $uuid)
            ->whereNull('user_id')
            ->where('status', 'active')
            ->first();

        if (!$guestCart) {
            return;
        }

        /*
         * Lấy hoặc tạo active cart của user.
         */
        $userCart = Cart::firstOrCreate(
            [
                'user_id' => $userId,
                'status' => 'active',
            ],
            [
                'uuid' => (string) Str::uuid(),
            ]
        );

        /*
         * Chuyển từng CartItem guest sang user.
         */
        foreach ($guestCart->items as $guestItem) {
            /*
             * Product::find() sẽ không trả về
             * Product đã soft delete.
             */
            $product = Product::find(
                $guestItem->product_id
            );

            /*
             * Product không còn tồn tại
             * → xóa CartItem không hợp lệ.
             */
            if (!$product) {
                $guestItem->delete();

                continue;
            }

            /*
             * Kiểm tra user đã có cùng sản phẩm chưa.
             */
            $userItem = $userCart->items()
                ->where(
                    'product_id',
                    $guestItem->product_id
                )
                ->first();

            /*
             * Đã có → cộng quantity.
             */
            if ($userItem) {
                $userItem->increment(
                    'quantity',
                    $guestItem->quantity
                );

                $campaign = $this->campaignService
                    ->getBestCampaignForProduct($product);

                $effectivePrice = $campaign
                    ? (int) $this->campaignService
                        ->getCampaignPrice($campaign, $product)
                    : (int) $product->price;

                /*
                 * Đồng bộ giá hiệu lực hiện tại.
                 */
                $userItem->update([
                    'price' => $effectivePrice,
                ]);

                $guestItem->delete();

                continue;
            }

            /*
             * User chưa có sản phẩm này
             * → chuyển trực tiếp CartItem từ guest cart
             * sang user cart.
             */
            $campaign = $this->campaignService
                ->getBestCampaignForProduct($product);

            $effectivePrice = $campaign
                ? (int) $this->campaignService
                    ->getCampaignPrice($campaign, $product)
                : (int) $product->price;

            $guestItem->update([
                'cart_id' => $userCart->id,
                'price' => $effectivePrice,
            ]);
        }

        /*
         * Guest cart không còn sử dụng.
         */
        $guestCart->delete();

        /*
         * Xóa cookie guest cart.
         */
        Cookie::queue(
            Cookie::forget(self::COOKIE_NAME)
        );
    }

    /**
     * ============================================================
     * DỌN CART ITEM KHÔNG CÒN PRODUCT
     * ============================================================
     */
    public function cleanupInvalidItems(): int
    {
        return $this->getCart()
            ->items()
            ->whereDoesntHave('product')
            ->delete();
    }
}