<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stage;
use App\Models\Tag;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Danh sách sản phẩm
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        /*
         * Product sử dụng SoftDeletes nên Product::query()
         * tự động loại các bản ghi deleted_at != NULL.
         */
        $query = Product::query()
            ->with('category');

        /*
         * Tìm kiếm.
         */
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where(
                    'products.name',
                    'like',
                    '%' . $search . '%'
                )
                    ->orWhere(
                        'products.slug',
                        'like',
                        '%' . $search . '%'
                    );
            });
        }

        /*
         * Lọc danh mục.
         */
        if ($request->filled('category_id')) {
            $query->where(
                'products.category_id',
                $request->category_id
            );
        }

        /*
         * Lọc trạng thái.
         */
        if ($request->status === 'active') {
            $query->where(
                'products.is_active',
                true
            );
        }

        if ($request->status === 'inactive') {
            $query->where(
                'products.is_active',
                false
            );
        }

        /*
         * Sắp hết hàng.
         */
        if ($request->boolean('low_stock')) {
            $query->where(
                'products.stock',
                '<=',
                10
            );
        }

        /*
         * Phân trang.
         */
        $products = $query
    ->join('categories', 'products.category_id', '=', 'categories.id')
    ->select('products.*')
    ->orderBy('categories.sort_order', 'asc')
    ->orderBy('categories.name', 'asc')
    ->orderBy('products.name', 'asc')
    ->paginate(10)
    ->withQueryString();
        /*
         * Danh mục cho filter.
         */
        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        /*
         * Số sản phẩm trong thùng rác.
         */
        $trashCount = Product::onlyTrashed()
            ->count();

        return view(
            'admin.products.index',
            compact(
                'products',
                'categories',
                'trashCount'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Form thêm sản phẩm
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $stages = Stage::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tags = Tag::orderBy('type')
            ->orderBy('name')
            ->get();

        return view(
            'admin.products.create',
            compact(
                'categories',
                'stages',
                'tags'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Lưu sản phẩm mới
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages()
        );

        /*
         * Tạo slug.
         */
        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        /*
         * Kiểm tra slug cả trong thùng rác.
         */
        if (
            Product::withTrashed()
                ->where(
                    'slug',
                    $validated['slug']
                )
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' =>
                        'Slug sản phẩm đã tồn tại.',
                ]);
        }

        /*
         * Checkbox trạng thái.
         */
        $validated['is_active'] =
            $request->boolean('is_active');

        /*
        |--------------------------------------------------------------------------
        | Ảnh đại diện → Cloudinary
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $validated['image'] =
                $this->uploadToCloudinary(
                    $request->file('image'),
                    'mommykids/products/main'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Gallery → Cloudinary
        |--------------------------------------------------------------------------
        */

        $gallery = [];

        if ($request->hasFile('images')) {
            foreach (
                $request->file('images')
                as $file
            ) {
                $gallery[] =
                    $this->uploadToCloudinary(
                        $file,
                        'mommykids/products/gallery'
                    );
            }
        }

        $validated['images'] = $gallery;

        /*
         * Không đưa các field phụ vào Product::create().
         */
        unset(
            $validated['stage_ids'],
            $validated['tag_ids'],
            $validated['remove_image'],
            $validated['remove_gallery']
        );

        /*
         * Tạo sản phẩm.
         */
        $product = Product::create(
            $validated
        );

        /*
         * Đồng bộ stage.
         */
        $product->stages()->sync(
            $request->input(
                'stage_ids',
                []
            )
        );

        /*
         * Đồng bộ tag.
         */
        $product->tags()->sync(
            $request->input(
                'tag_ids',
                []
            )
        );

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Thêm sản phẩm thành công.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Form sửa sản phẩm
    |--------------------------------------------------------------------------
    */

    public function edit(Product $product)
    {
        $product->load([
            'category',
            'stages',
            'tags',
        ]);

        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $stages = Stage::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tags = Tag::orderBy('type')
            ->orderBy('name')
            ->get();

        return view(
            'admin.products.edit',
            compact(
                'product',
                'categories',
                'stages',
                'tags'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cập nhật sản phẩm
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Product $product
    ) {
        $rules = $this->rules();

        /*
         * Slug update sẽ tự kiểm tra riêng
         * cả sản phẩm đã soft delete.
         */
        $rules['slug'] = [
            'nullable',
            'string',
            'max:255',
        ];

        $validated = $request->validate(
            $rules,
            $this->messages()
        );

        /*
         * Chuẩn hóa slug.
         */
        $validated['slug'] =
            $this->makeSlug(
                $validated['slug'] ?? null,
                $validated['name']
            );

        /*
         * Không cho trùng slug với sản phẩm khác,
         * kể cả sản phẩm đang trong thùng rác.
         */
        $slugExists =
            Product::withTrashed()
                ->where(
                    'slug',
                    $validated['slug']
                )
                ->where(
                    'id',
                    '!=',
                    $product->id
                )
                ->exists();

        if ($slugExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' =>
                        'Slug sản phẩm đã tồn tại.',
                ]);
        }

        /*
         * Checkbox trạng thái.
         */
        $validated['is_active'] =
            $request->boolean('is_active');

        /*
        |--------------------------------------------------------------------------
        | Ảnh đại diện
        |--------------------------------------------------------------------------
        */

        /*
         * Người dùng chủ động xóa ảnh.
         */
        if (
            $request->boolean('remove_image')
        ) {
            $this->deleteStoredImage(
                $product->image
            );

            $validated['image'] = null;
        }

        /*
         * Upload ảnh mới.
         *
         * Upload Cloudinary thành công trước,
         * sau đó mới xóa ảnh cũ để tránh mất ảnh
         * nếu upload thất bại.
         */
        if ($request->hasFile('image')) {
            $newImage =
                $this->uploadToCloudinary(
                    $request->file('image'),
                    'mommykids/products/main'
                );

            /*
             * Xóa ảnh cũ.
             */
            $this->deleteStoredImage(
                $product->image
            );

            /*
             * Lưu URL Cloudinary mới.
             */
            $validated['image'] =
                $newImage;
        }

        /*
        |--------------------------------------------------------------------------
        | Gallery
        |--------------------------------------------------------------------------
        */

        $gallery =
            $product->images ?? [];

        /*
         * Danh sách ảnh muốn xóa.
         */
        $removeGallery =
            $request->input(
                'remove_gallery',
                []
            );

        if (
            is_array($removeGallery)
        ) {
            foreach (
                $removeGallery
                as $imageToRemove
            ) {
                /*
                 * Chỉ xóa nếu ảnh thực sự nằm
                 * trong gallery sản phẩm.
                 */
                if (
                    in_array(
                        $imageToRemove,
                        $gallery,
                        true
                    )
                ) {
                    $this->deleteStoredImage(
                        $imageToRemove
                    );
                }
            }

            /*
             * Loại ảnh đã xóa khỏi array.
             */
            $gallery = array_values(
                array_filter(
                    $gallery,
                    fn ($image) =>
                        !in_array(
                            $image,
                            $removeGallery,
                            true
                        )
                )
            );
        }

        /*
         * Upload thêm gallery mới.
         */
        if (
            $request->hasFile('images')
        ) {
            foreach (
                $request->file('images')
                as $file
            ) {
                $gallery[] =
                    $this->uploadToCloudinary(
                        $file,
                        'mommykids/products/gallery'
                    );
            }
        }

        $validated['images'] =
            $gallery;

        /*
         * Xóa field không thuộc products.
         */
        unset(
            $validated['stage_ids'],
            $validated['tag_ids'],
            $validated['remove_image'],
            $validated['remove_gallery']
        );

        /*
         * Update database.
         */
        $product->update(
            $validated
        );

        /*
         * Đồng bộ stage.
         */
        $product->stages()->sync(
            $request->input(
                'stage_ids',
                []
            )
        );

        /*
         * Đồng bộ tag.
         */
        $product->tags()->sync(
            $request->input(
                'tag_ids',
                []
            )
        );

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Cập nhật sản phẩm thành công.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa mềm
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Product $product
    ) {
        /*
         * Lưu ID người thực hiện.
         *
         * Hiện project của bạn vẫn đang dùng
         * auth web ở một số khu vực admin,
         * nên auth()->id() sẽ lấy user hiện tại.
         */
        $product->update([
            'deleted_by' =>
                auth()->id(),

            /*
             * Nếu sản phẩm từng được restore,
             * xóa lần nữa thì reset thông tin restore.
             */
            'restored_by' => null,
            'restored_at' => null,
        ]);

        /*
         * Soft delete:
         *
         * - chỉ set deleted_at
         * - không xóa ảnh
         * - không detach Stage
         * - không detach Tag
         */
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Sản phẩm đã được chuyển vào thùng rác.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Thùng rác
    |--------------------------------------------------------------------------
    */

    public function trash(
        Request $request
    ) {
        /*
         * Chỉ lấy sản phẩm đã soft delete.
         */
        $query =
            Product::onlyTrashed()
                ->with('category');

        /*
         * Search.
         */
        if (
            $request->filled('search')
        ) {
            $search =
                trim(
                    $request->search
                );

            $query->where(
                function ($q) use ($search) {
                    $q->where(
                        'name',
                        'like',
                        '%' . $search . '%'
                    )
                        ->orWhere(
                            'slug',
                            'like',
                            '%' . $search . '%'
                        );
                }
            );
        }

        /*
         * Mới xóa hiện trước.
         */
        $products = $query
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.products.trash',
            compact('products')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Khôi phục
    |--------------------------------------------------------------------------
    */

    public function restore(
        string $id
    ) {
        $product =
            Product::onlyTrashed()
                ->findOrFail($id);

        /*
         * Laravel đưa deleted_at về NULL.
         */
        $product->restore();

        /*
         * Ghi lịch sử restore.
         *
         * deleted_by vẫn giữ nguyên để biết
         * trước đó ai đã xóa sản phẩm.
         */
        $product->update([
            'restored_by' =>
                auth()->id(),

            'restored_at' =>
                now(),
        ]);

        return redirect()
            ->route(
                'admin.products.trash'
            )
            ->with(
                'success',
                'Khôi phục sản phẩm thành công.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa vĩnh viễn
    |--------------------------------------------------------------------------
    */

    public function forceDelete(
        string $id
    ) {
        $product =
            Product::onlyTrashed()
                ->findOrFail($id);

        /*
         * Xóa ảnh đại diện.
         *
         * Nếu là Cloudinary → xóa Cloudinary.
         * Nếu là local cũ → xóa storage.
         */
        $this->deleteStoredImage(
            $product->image
        );

        /*
         * Xóa gallery.
         */
        foreach (
            $product->images ?? []
            as $image
        ) {
            $this->deleteStoredImage(
                $image
            );
        }

        /*
         * Xóa quan hệ pivot.
         */
        $product->stages()
            ->detach();

        $product->tags()
            ->detach();

        /*
         * Xóa thật khỏi DB.
         */
        $product->forceDelete();

        return redirect()
            ->route(
                'admin.products.trash'
            )
            ->with(
                'success',
                'Đã xóa vĩnh viễn sản phẩm.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function rules(): array
    {
        return [
            /*
             * Category.
             */
            'category_id' => [
                'required',
                'exists:categories,id',
            ],

            /*
             * Tên.
             */
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * Slug.
             */
            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * Mô tả.
             */
            'description' => [
                'nullable',
                'string',
            ],

            /*
             * Giá.
             */
            'price' => [
                'required',
                'integer',
                'min:0',
            ],

            /*
             * Giá cũ.
             */
            'old_price' => [
                'nullable',
                'integer',
                'min:0',
            ],

            /*
             * Giảm giá.
             */
            'discount_percent' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],

            /*
             * Tồn kho.
             */
            'stock' => [
                'required',
                'integer',
                'min:0',
            ],

            /*
             * Status.
             */
            'is_active' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Ảnh đại diện
            |--------------------------------------------------------------------------
            */

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            /*
            |--------------------------------------------------------------------------
            | Gallery
            |--------------------------------------------------------------------------
            */

            'images' => [
                'nullable',
                'array',
                'max:8',
            ],

            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            /*
             * Remove main image.
             */
            'remove_image' => [
                'nullable',
                'boolean',
            ],

            /*
             * Remove gallery.
             */
            'remove_gallery' => [
                'nullable',
                'array',
            ],

            /*
            |--------------------------------------------------------------------------
            | Stage
            |--------------------------------------------------------------------------
            */

            'stage_ids' => [
                'nullable',
                'array',
            ],

            'stage_ids.*' => [
                'exists:stages,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Tag
            |--------------------------------------------------------------------------
            */

            'tag_ids' => [
                'nullable',
                'array',
            ],

            'tag_ids.*' => [
                'exists:tags,id',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Validation messages
    |--------------------------------------------------------------------------
    */

    private function messages(): array
    {
        return [
            'category_id.required' =>
                'Vui lòng chọn danh mục.',

            'category_id.exists' =>
                'Danh mục không hợp lệ.',

            'name.required' =>
                'Vui lòng nhập tên sản phẩm.',

            'price.required' =>
                'Vui lòng nhập giá sản phẩm.',

            'price.integer' =>
                'Giá sản phẩm phải là số.',

            'price.min' =>
                'Giá sản phẩm không được nhỏ hơn 0.',

            'old_price.integer' =>
                'Giá cũ phải là số.',

            'old_price.min' =>
                'Giá cũ không được nhỏ hơn 0.',

            'discount_percent.integer' =>
                'Phần trăm giảm phải là số nguyên.',

            'discount_percent.min' =>
                'Phần trăm giảm không được nhỏ hơn 0.',

            'discount_percent.max' =>
                'Phần trăm giảm không được lớn hơn 100.',

            'stock.required' =>
                'Vui lòng nhập tồn kho.',

            'stock.integer' =>
                'Tồn kho phải là số nguyên.',

            'stock.min' =>
                'Tồn kho không được nhỏ hơn 0.',

            'image.image' =>
                'Ảnh đại diện không hợp lệ.',

            'image.mimes' =>
                'Ảnh đại diện chỉ nhận JPG, JPEG, PNG hoặc WEBP.',

            'image.max' =>
                'Ảnh đại diện không được lớn hơn 4MB.',

            'images.max' =>
                'Chỉ được tải tối đa 8 ảnh chi tiết.',

            'images.*.image' =>
                'Một ảnh chi tiết không hợp lệ.',

            'images.*.mimes' =>
                'Ảnh chi tiết chỉ nhận JPG, JPEG, PNG hoặc WEBP.',

            'images.*.max' =>
                'Mỗi ảnh chi tiết không được lớn hơn 4MB.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Tạo slug
    |--------------------------------------------------------------------------
    */

    private function makeSlug(
        ?string $slug,
        string $name
    ): string {
        return Str::slug(
            filled($slug)
                ? $slug
                : $name
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Tạo Cloudinary instance
    |--------------------------------------------------------------------------
    */

    private function cloudinary(): Cloudinary
    {
        /*
         * config/cloudinary.php:
         *
         * 'cloud_url' => env('CLOUDINARY_URL')
         */
        $cloudUrl =
            config(
                'cloudinary.cloud_url'
            );

        if (!$cloudUrl) {
            throw new RuntimeException(
                'CLOUDINARY_URL chưa được cấu hình. '
                . 'Kiểm tra file .env và config/cloudinary.php.'
            );
        }

        return new Cloudinary(
            $cloudUrl
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Upload Cloudinary
    |--------------------------------------------------------------------------
    */

    private function uploadToCloudinary(
        UploadedFile $file,
        string $folder
    ): string {
        /*
         * QUAN TRỌNG:
         *
         * Cloudinary PHP SDK 2.x:
         *
         * ĐÚNG:
         * ->uploadApi()
         *
         * SAI:
         * ->uploadApi
         */

        $result =
            $this->cloudinary()
                ->uploadApi()
                ->upload(
                    $file->getRealPath(),
                    [
                        /*
                         * Folder trên Cloudinary.
                         */
                        'folder' =>
                            $folder,

                        /*
                         * Chỉ image.
                         */
                        'resource_type' =>
                            'image',

                        /*
                         * Giữ tên file gốc.
                         */
                        'use_filename' =>
                            true,

                        /*
                         * Thêm phần unique tránh trùng tên.
                         */
                        'unique_filename' =>
                            true,

                        /*
                         * Không ghi đè asset cũ.
                         */
                        'overwrite' =>
                            false,
                    ]
                );

        /*
         * Cloudinary trả HTTPS URL.
         */
        $url =
            $result['secure_url']
            ?? null;

        if (!$url) {
            throw new RuntimeException(
                'Cloudinary upload thành công '
                . 'nhưng không trả về secure_url.'
            );
        }

        return $url;
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa ảnh
    |--------------------------------------------------------------------------
    |
    | Hỗ trợ đồng thời:
    |
    | 1. Ảnh Cloudinary mới
    | 2. Ảnh local Laravel cũ
    | 3. URL ngoài không thuộc Cloudinary
    |
    */

    private function deleteStoredImage(
        ?string $image
    ): void {
        if (!$image) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Cloudinary
        |--------------------------------------------------------------------------
        */

        if (
            $this->isCloudinaryUrl(
                $image
            )
        ) {
            $publicId =
                $this
                    ->extractCloudinaryPublicId(
                        $image
                    );

            if (!$publicId) {
                return;
            }

            try {
                /*
                 * QUAN TRỌNG:
                 *
                 * phải dùng uploadApi()
                 * chứ không phải uploadApi.
                 */
                $this->cloudinary()
                    ->uploadApi()
                    ->destroy(
                        $publicId,
                        [
                            'resource_type' =>
                                'image',

                            /*
                             * Làm CDN xóa cache ảnh.
                             */
                            'invalidate' =>
                                true,
                        ]
                    );
            } catch (\Throwable $e) {
                /*
                 * Không làm hỏng thao tác database
                 * chỉ vì Cloudinary xóa asset lỗi.
                 */
                report($e);
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | URL ngoài
        |--------------------------------------------------------------------------
        */

        if (
            Str::startsWith(
                $image,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            /*
             * Không tự xóa tài nguyên
             * của website/service khác.
             */
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Ảnh local cũ
        |--------------------------------------------------------------------------
        |
        | Ví dụ:
        |
        | products/main/abc.jpg
        |
        */

        Storage::disk('public')
            ->delete(
                $image
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Kiểm tra URL Cloudinary
    |--------------------------------------------------------------------------
    */

    private function isCloudinaryUrl(
        string $url
    ): bool {
        $host =
            parse_url(
                $url,
                PHP_URL_HOST
            );

        if (!is_string($host)) {
            return false;
        }

        return
            $host ===
                'res.cloudinary.com'
            ||
            Str::endsWith(
                $host,
                '.cloudinary.com'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Lấy Cloudinary public_id từ URL
    |--------------------------------------------------------------------------
    |
    | Ví dụ:
    |
    | https://res.cloudinary.com/xxx/image/upload/
    | v1234567890/mommykids/products/main/meiji_abc.jpg
    |
    | Sẽ lấy:
    |
    | mommykids/products/main/meiji_abc
    |
    */

    private function extractCloudinaryPublicId(
        string $url
    ): ?string {
        $path =
            parse_url(
                $url,
                PHP_URL_PATH
            );

        if (!is_string($path)) {
            return null;
        }

        /*
         * Tìm phần /image/upload/.
         */
        $marker =
            '/image/upload/';

        $position =
            strpos(
                $path,
                $marker
            );

        if ($position === false) {
            return null;
        }

        /*
         * Lấy phần sau /image/upload/.
         */
        $relativePath =
            substr(
                $path,
                $position
                + strlen($marker)
            );

        /*
         * Bỏ Cloudinary version.
         *
         * Ví dụ:
         *
         * v1756300000/...
         *
         * =>
         *
         * mommykids/...
         */
        $relativePath =
            preg_replace(
                '#^v\d+/#',
                '',
                $relativePath
            );

        if (!$relativePath) {
            return null;
        }

        /*
         * Bỏ phần mở rộng file:
         *
         * .jpg
         * .jpeg
         * .png
         * .webp
         */
        $publicId =
            preg_replace(
                '/\.[^\.\/]+$/',
                '',
                $relativePath
            );

        if (!$publicId) {
            return null;
        }

        /*
         * Decode ký tự URL.
         */
        return rawurldecode(
            ltrim(
                $publicId,
                '/'
            )
        );
    }
}