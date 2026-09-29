<?php

namespace App\Http\Controllers\Admin\Concerns;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

trait ProductHelpers
{
    private function rules(?int $productId = null): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            // Thông tin chi tiết sản phẩm
            'origin' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'ingredients' => ['nullable', 'string'],
            'usage_instructions' => ['nullable', 'string'],
            'storage_instructions' => ['nullable', 'string'],
            'warning' => ['nullable', 'string'],

            // Thông tin định danh và kho
            'sku' => [
                'nullable',
                'string',
                'max:50',
                $productId
                    ? 'unique:products,sku,' . $productId
                    : 'unique:products,sku',
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            'low_stock_alert' => ['required', 'integer', 'min:0'],

            // Điểm nổi bật sản phẩm
            'highlights' => ['nullable', 'array'],
            'highlights.items' => ['nullable', 'array', 'max:4'],
            'highlights.items.*.title' => [
                'nullable',
                'string',
                'max:100',
            ],
            'highlights.items.*.subtitle' => [
                'nullable',
                'string',
                'max:150',
            ],
            'highlights.items.*.icon' => [
                'nullable',
                'in:shield,brain,digest,heart,bone,eye,check',
            ],
            'highlights.message' => [
                'nullable',
                'string',
                'max:255',
            ],
            'highlights.submessage' => [
                'nullable',
                'string',
                'max:255',
            ],

            // Giá và tồn kho
            'price' => ['required', 'integer', 'min:0'],
            'old_price' => [
                'nullable',
                'integer',
                'min:0',
                'gte:price',
            ],
            'discount_percent' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'stock' => ['required', 'integer', 'min:0'],

            // Thông số vận chuyển
            'weight_grams' => ['required', 'integer', 'min:1'],
            'length_cm' => ['required', 'integer', 'min:1'],
            'width_cm' => ['required', 'integer', 'min:1'],
            'height_cm' => ['required', 'integer', 'min:1'],

            // Trạng thái
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],

            // Ảnh đại diện
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            // Ảnh chi tiết
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            // Xóa ảnh
            'remove_image' => ['nullable', 'boolean'],
            'remove_gallery' => ['nullable', 'array'],

            // Giai đoạn và thẻ sản phẩm
            'stage_ids' => ['nullable', 'array'],
            'stage_ids.*' => ['exists:stages,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
        ];
    }

    private function messages(): array
    {
        return [
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục không hợp lệ.',
            'name.required' => 'Vui lòng nhập tên sản phẩm.',

            'price.required' => 'Vui lòng nhập giá sản phẩm.',
            'price.integer' => 'Giá sản phẩm phải là số.',
            'price.min' => 'Giá sản phẩm không được nhỏ hơn 0.',

            'old_price.integer' => 'Giá cũ phải là số.',
            'old_price.min' => 'Giá cũ không được nhỏ hơn 0.',
            'old_price.gte' => 'Giá cũ phải lớn hơn hoặc bằng giá bán.',

            'discount_percent.integer' => 'Phần trăm giảm phải là số nguyên.',
            'discount_percent.min' => 'Phần trăm giảm không được nhỏ hơn 0.',
            'discount_percent.max' => 'Phần trăm giảm không được lớn hơn 100.',

            'sku.unique' => 'Mã SKU này đã được sử dụng cho một sản phẩm khác.',
            'sku.max' => 'Mã SKU không được vượt quá 50 ký tự.',
            'code.max' => 'Mã Barcode không được vượt quá 50 ký tự.',

            'cost_price.integer' => 'Giá vốn phải là số.',
            'cost_price.min' => 'Giá vốn không được nhỏ hơn 0.',

            'low_stock_alert.required' => 'Vui lòng nhập mức cảnh báo sắp hết hàng.',
            'low_stock_alert.integer' => 'Mức cảnh báo sắp hết hàng phải là số nguyên.',
            'low_stock_alert.min' => 'Mức cảnh báo sắp hết hàng không được nhỏ hơn 0.',

            'stock.required' => 'Vui lòng nhập tồn kho.',
            'stock.integer' => 'Tồn kho phải là số nguyên.',
            'stock.min' => 'Tồn kho không được nhỏ hơn 0.',

            'weight_grams.required' => 'Vui lòng nhập khối lượng sản phẩm.',
            'weight_grams.integer' => 'Khối lượng sản phẩm phải là số nguyên.',
            'weight_grams.min' => 'Khối lượng sản phẩm phải lớn hơn 0 gram.',

            'length_cm.required' => 'Vui lòng nhập chiều dài sản phẩm.',
            'length_cm.integer' => 'Chiều dài sản phẩm phải là số nguyên.',
            'length_cm.min' => 'Chiều dài sản phẩm phải lớn hơn 0 cm.',

            'width_cm.required' => 'Vui lòng nhập chiều rộng sản phẩm.',
            'width_cm.integer' => 'Chiều rộng sản phẩm phải là số nguyên.',
            'width_cm.min' => 'Chiều rộng sản phẩm phải lớn hơn 0 cm.',

            'height_cm.required' => 'Vui lòng nhập chiều cao sản phẩm.',
            'height_cm.integer' => 'Chiều cao sản phẩm phải là số nguyên.',
            'height_cm.min' => 'Chiều cao sản phẩm phải lớn hơn 0 cm.',

            'highlights.items.max' => 'Chỉ được nhập tối đa 4 điểm nổi bật.',
            'highlights.items.*.title.max' => 'Tiêu đề điểm nổi bật không được vượt quá 100 ký tự.',
            'highlights.items.*.subtitle.max' => 'Nội dung ngắn của điểm nổi bật không được vượt quá 150 ký tự.',
            'highlights.items.*.icon.in' => 'Icon điểm nổi bật không hợp lệ.',
            'highlights.message.max' => 'Thông điệp nổi bật không được vượt quá 255 ký tự.',
            'highlights.submessage.max' => 'Nội dung phụ không được vượt quá 255 ký tự.',

            'image.image' => 'Ảnh đại diện không hợp lệ.',
            'image.mimes' => 'Ảnh đại diện chỉ nhận JPG, JPEG, PNG hoặc WEBP.',
            'image.max' => 'Ảnh đại diện không được lớn hơn 4MB.',

            'images.max' => 'Chỉ được tải tối đa 8 ảnh chi tiết.',
            'images.*.image' => 'Một ảnh chi tiết không hợp lệ.',
            'images.*.mimes' => 'Ảnh chi tiết chỉ nhận JPG, JPEG, PNG hoặc WEBP.',
            'images.*.max' => 'Mỗi ảnh chi tiết không được lớn hơn 4MB.',
        ];
    }

    /**
     * Tính phần trăm giảm giá từ giá bán và giá cũ.
     */
    private function calculateDiscountPercent(
        int $price,
        ?int $oldPrice
    ): int {
        if (!$oldPrice || $oldPrice <= 0 || $oldPrice <= $price) {
            return 0;
        }

        return (int) round(
            (($oldPrice - $price) / $oldPrice) * 100
        );
    }

    /**
     * Chuẩn hóa danh sách điểm nổi bật.
     */
    private function normalizeHighlights(?array $highlights): ?array
    {
        if (!$highlights) {
            return null;
        }

        $items = collect($highlights['items'] ?? [])
            ->map(function ($item) {
                return [
                    'title' => filled($item['title'] ?? null)
                        ? trim((string) $item['title'])
                        : null,
                    'subtitle' => filled($item['subtitle'] ?? null)
                        ? trim((string) $item['subtitle'])
                        : null,
                    'icon' => filled($item['icon'] ?? null)
                        ? (string) $item['icon']
                        : 'check',
                ];
            })
            ->filter(fn ($item) => filled($item['title']))
            ->take(4)
            ->values()
            ->all();

        $message = filled($highlights['message'] ?? null)
            ? trim((string) $highlights['message'])
            : null;

        $submessage = filled($highlights['submessage'] ?? null)
            ? trim((string) $highlights['submessage'])
            : null;

        if (empty($items) && !$message && !$submessage) {
            return null;
        }

        return [
            'items' => $items,
            'message' => $message,
            'submessage' => $submessage,
        ];
    }

    /**
     * Tạo slug từ tên sản phẩm nếu chưa có.
     */
    private function makeSlug(?string $slug, string $name): string
    {
        return Str::slug(
            filled($slug) ? $slug : $name
        );
    }

    /**
     * Khởi tạo Cloudinary.
     */
    private function cloudinary(): Cloudinary
    {
        $cloudUrl = config('cloudinary.cloud_url');

        if (!$cloudUrl) {
            throw new RuntimeException(
                'CLOUDINARY_URL chưa được cấu hình. '
                . 'Kiểm tra file .env và config/cloudinary.php.'
            );
        }

        return new Cloudinary($cloudUrl);
    }

    /**
     * Upload ảnh lên Cloudinary.
     */
    private function uploadToCloudinary(
        UploadedFile $file,
        string $folder
    ): string {
        $result = $this->cloudinary()
            ->uploadApi()
            ->upload(
                $file->getRealPath(),
                [
                    'folder' => $folder,
                    'resource_type' => 'image',
                    'use_filename' => true,
                    'unique_filename' => true,
                    'overwrite' => false,
                ]
            );

        $url = $result['secure_url'] ?? null;

        if (!$url) {
            throw new RuntimeException(
                'Cloudinary upload thành công '
                . 'nhưng không trả về secure_url.'
            );
        }

        return $url;
    }

    /**
     * Xóa ảnh Cloudinary hoặc ảnh lưu trong storage.
     */
    private function deleteStoredImage(?string $image): void
    {
        if (!$image) {
            return;
        }

        if ($this->isCloudinaryUrl($image)) {
            $publicId = $this->extractCloudinaryPublicId($image);

            if (!$publicId) {
                return;
            }

            try {
                $this->cloudinary()
                    ->uploadApi()
                    ->destroy(
                        $publicId,
                        [
                            'resource_type' => 'image',
                            'invalidate' => true,
                        ]
                    );
            } catch (\Throwable $e) {
                report($e);
            }

            return;
        }

        // Không xóa ảnh từ URL của dịch vụ bên ngoài.
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete($image);
    }

    /**
     * Kiểm tra URL thuộc Cloudinary.
     */
    private function isCloudinaryUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (!is_string($host)) {
            return false;
        }

        return $host === 'res.cloudinary.com'
            || Str::endsWith($host, '.cloudinary.com');
    }

    /**
     * Trích xuất public_id của ảnh Cloudinary.
     */
    private function extractCloudinaryPublicId(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path)) {
            return null;
        }

        $marker = '/image/upload/';
        $position = strpos($path, $marker);

        if ($position === false) {
            return null;
        }

        $relativePath = substr(
            $path,
            $position + strlen($marker)
        );

        $relativePath = preg_replace(
            '#^v\d+/#',
            '',
            $relativePath
        );

        if (!$relativePath) {
            return null;
        }

        $publicId = preg_replace(
            '/\.[^\.\/]+$/',
            '',
            $relativePath
        );

        if (!$publicId) {
            return null;
        }

        return rawurldecode(
            ltrim($publicId, '/')
        );
    }
}
