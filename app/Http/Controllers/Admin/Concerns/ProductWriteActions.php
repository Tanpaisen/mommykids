<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait ProductWriteActions
{
    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages()
        );

        $validated['highlights'] = $this->normalizeHighlights(
            $validated['highlights'] ?? null
        );

        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

     
        if (Product::withTrashed()->where('slug', $validated['slug'])->exists()) {
            return back()->withInput()->withErrors([
                'slug' => 'Slug sản phẩm đã tồn tại.',
            ]);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        $validated['discount_percent'] = $this->calculateDiscountPercent(
            (int) $validated['price'],
            isset($validated['old_price'])
                ? (int) $validated['old_price']
                : null
        );

        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadToCloudinary(
                $request->file('image'),
                'mommykids/products/main'
            );
        }

        // Upload danh sách ảnh chi tiết
        $gallery = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $gallery[] = $this->uploadToCloudinary(
                    $file,
                    'mommykids/products/gallery'
                );
            }
        }

        $validated['images'] = $gallery;

        // Tạo SKU tự động khi không nhập
        $validated['sku'] = $request->sku ?: strtoupper(Str::random(8));

        // Tồn kho ban đầu được nhập thông qua InventoryService
        $initialStock = (int) ($validated['stock'] ?? 0);
        $validated['stock'] = 0;

        // Loại bỏ các trường không thuộc bảng products
        unset(
            $validated['stage_ids'],
            $validated['tag_ids'],
            $validated['remove_image'],
            $validated['remove_gallery']
        );

        DB::transaction(function () use ($validated, $request, $initialStock) {
            // Tạo sản phẩm
            $product = Product::create($validated);

            // Đồng bộ các giai đoạn
            $product->stages()->sync(
                $request->input('stage_ids', [])
            );

            // Đồng bộ các thẻ
            $product->tags()->sync(
                $request->input('tag_ids', [])
            );

            // Ghi nhận nhập kho ban đầu
            if ($initialStock > 0) {
                app(InventoryService::class)->import(
                    $product,
                    $initialStock,
                    null,
                    'Nhập kho ban đầu khi tạo sản phẩm'
                );
            }
        });

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công.');
    }

    public function update(Request $request, Product $product)
    {
        $rules = $this->rules($product->id);

        // Tồn kho được quản lý riêng trong module Kho
        unset($rules['stock']);

        // Kiểm tra slug riêng, bao gồm sản phẩm đã xóa mềm
        $rules['slug'] = [
            'nullable',
            'string',
            'max:255',
        ];

        $validated = $request->validate(
            $rules,
            $this->messages()
        );

        $validated['highlights'] = $this->normalizeHighlights(
            $validated['highlights'] ?? null
        );

        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        // Kiểm tra trùng slug với sản phẩm khác
        $slugExists = Product::withTrashed()
            ->where('slug', $validated['slug'])
            ->where('id', '!=', $product->id)
            ->exists();

        if ($slugExists) {
            return back()->withInput()->withErrors([
                'slug' => 'Slug sản phẩm đã tồn tại.',
            ]);
        }

        // Cập nhật trạng thái
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        // Tính lại phần trăm giảm giá
        $validated['discount_percent'] = $this->calculateDiscountPercent(
            (int) $validated['price'],
            isset($validated['old_price'])
                ? (int) $validated['old_price']
                : null
        );

        // Người dùng chủ động xóa ảnh đại diện
        if ($request->boolean('remove_image')) {
            $this->deleteStoredImage($product->image);
            $validated['image'] = null;
        }

        // Upload ảnh mới thành công trước khi xóa ảnh cũ
        if ($request->hasFile('image')) {
            $newImage = $this->uploadToCloudinary(
                $request->file('image'),
                'mommykids/products/main'
            );

            $this->deleteStoredImage($product->image);
            $validated['image'] = $newImage;
        }

        // Giữ lại danh sách ảnh gallery hiện tại
        $gallery = $product->images ?? [];

        // Lấy danh sách ảnh yêu cầu xóa
        $removeGallery = $request->input('remove_gallery', []);

        if (is_array($removeGallery)) {
            foreach ($removeGallery as $imageToRemove) {
                // Chỉ xóa ảnh thực sự thuộc gallery sản phẩm
                if (in_array($imageToRemove, $gallery, true)) {
                    $this->deleteStoredImage($imageToRemove);
                }
            }

            // Loại các ảnh đã xóa khỏi danh sách
            $gallery = array_values(
                array_filter(
                    $gallery,
                    fn ($image) => !in_array(
                        $image,
                        $removeGallery,
                        true
                    )
                )
            );
        }

        // Upload thêm ảnh gallery mới
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $gallery[] = $this->uploadToCloudinary(
                    $file,
                    'mommykids/products/gallery'
                );
            }
        }

        $validated['images'] = $gallery;

        // Giữ logic SKU hiện tại
        $validated['sku'] = $request->sku ?: strtoupper(Str::random(8));

        // Loại bỏ các trường không thuộc bảng products
        unset(
            $validated['stage_ids'],
            $validated['tag_ids'],
            $validated['remove_image'],
            $validated['remove_gallery'],
            $validated['stock']
        );

        // Cập nhật dữ liệu sản phẩm
        $product->update($validated);

        // Đồng bộ lại các giai đoạn
        $product->stages()->sync(
            $request->input('stage_ids', [])
        );

        // Đồng bộ lại các thẻ
        $product->tags()->sync(
            $request->input('tag_ids', [])
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công.');
    }
}
