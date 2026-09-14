<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tag;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Danh mục
        |--------------------------------------------------------------------------
        */

        $categoryQuery = Category::query();

        if ($request->filled('category_search')) {
            $search = trim($request->category_search);

            $categoryQuery->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('category_status')) {
            if ($request->category_status === 'active') {
                $categoryQuery->where('is_active', true);
            }

            if ($request->category_status === 'inactive') {
                $categoryQuery->where('is_active', false);
            }
        }

        $categories = $categoryQuery
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(
                10,
                ['*'],
                'category_page'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Tag
        |--------------------------------------------------------------------------
        */

        $tagQuery = Tag::query();

        if ($request->filled('tag_search')) {
            $search = trim($request->tag_search);

            $tagQuery->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('tag_type')) {
            $tagQuery->where(
                'type',
                $request->tag_type
            );
        }

        $tags = $tagQuery
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(
                10,
                ['*'],
                'tag_page'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Số lượng thùng rác riêng
        |--------------------------------------------------------------------------
        */

        $categoryTrashCount =
            Category::onlyTrashed()->count();

        $tagTrashCount =
            Tag::onlyTrashed()->count();

        return view(
            'admin.categories.index',
            compact(
                'categories',
                'tags',
                'categoryTrashCount',
                'tagTrashCount'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view(
            'admin.categories.create'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'icon' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],

                'sort_order' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],

                'is_active' => [
                    'nullable',
                ],
            ],
            $this->messages()
        );

        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        /*
         * Kiểm tra cả danh mục đã xóa mềm.
         */
        if (
            Category::withTrashed()
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
                        'Slug danh mục đã tồn tại.',
                ]);
        }

        $validated['sort_order'] =
            $validated['sort_order'] ?? 0;

        $validated['is_active'] =
            $request->boolean('is_active');

        /*
        |--------------------------------------------------------------------------
        | Upload ảnh/icon danh mục lên Cloudinary
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $validated['image'] =
                $this->uploadToCloudinary(
                    $request->file('image'),
                    'mommykids/categories'
                );
        }

        Category::create($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                'Thêm danh mục thành công.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(Category $category)
    {
        return view(
            'admin.categories.edit',
            compact('category')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Category $category
    ) {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'icon' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'image' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],

                'remove_image' => [
                    'nullable',
                    'boolean',
                ],

                'sort_order' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],

                'is_active' => [
                    'nullable',
                ],
            ],
            $this->messages()
        );

        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        /*
         * Kiểm tra slug kể cả record trong thùng rác.
         */
        $slugExists = Category::withTrashed()
            ->where(
                'slug',
                $validated['slug']
            )
            ->where(
                'id',
                '!=',
                $category->id
            )
            ->exists();

        if ($slugExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' =>
                        'Slug danh mục đã tồn tại.',
                ]);
        }

        $validated['sort_order'] =
            $validated['sort_order'] ?? 0;

        $validated['is_active'] =
            $request->boolean('is_active');

        /*
        |--------------------------------------------------------------------------
        | Ảnh/Icon danh mục
        |--------------------------------------------------------------------------
        |
        | Nếu upload ảnh mới:
        | 1. Upload ảnh mới lên Cloudinary trước
        | 2. Thành công mới xóa ảnh cũ
        | 3. Lưu URL mới vào database
        |
        | Nếu chỉ chọn "xóa ảnh":
        | - xóa ảnh cũ
        | - image = NULL
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $newImage =
                $this->uploadToCloudinary(
                    $request->file('image'),
                    'mommykids/categories'
                );

            $this->deleteStoredImage(
                $category->image
            );

            $validated['image'] =
                $newImage;
        } elseif (
            $request->boolean('remove_image')
        ) {
            $this->deleteStoredImage(
                $category->image
            );

            $validated['image'] = null;
        }

        /*
         * remove_image không phải cột trong bảng categories.
         */
        unset(
            $validated['remove_image']
        );

        $category->update($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                'Cập nhật danh mục thành công.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa mềm danh mục
    |--------------------------------------------------------------------------
    */

    public function destroy(Category $category)
    {
        /*
         * Không cho xóa danh mục đang có sản phẩm.
         */
        if ($category->products()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with(
                    'error',
                    'Không thể chuyển danh mục đang có sản phẩm vào thùng rác.'
                );
        }

        /*
         * Không xóa ảnh khi soft delete.
         */
        $category->update([
            'deleted_by' => auth()->id(),
            'restored_by' => null,
            'restored_at' => null,
        ]);

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                'Danh mục đã được chuyển vào thùng rác.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Thùng rác danh mục
    |--------------------------------------------------------------------------
    */

    public function trash(Request $request)
    {
        $query = Category::onlyTrashed();

        if ($request->filled('search')) {
            $search = trim(
                $request->search
            );

            $query->where(
                function ($q) use ($search) {
                    $q
                        ->where(
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

        $categories = $query
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.categories.trash',
            compact('categories')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Khôi phục danh mục
    |--------------------------------------------------------------------------
    */

    public function restore(string $id)
    {
        $category = Category::onlyTrashed()
            ->findOrFail($id);

        /*
         * Kiểm tra slug trước khi restore.
         */
        $slugExists = Category::query()
            ->where(
                'slug',
                $category->slug
            )
            ->exists();

        if ($slugExists) {
            return redirect()
                ->route(
                    'admin.categories.trash'
                )
                ->with(
                    'error',
                    'Không thể khôi phục vì slug danh mục đã được sử dụng.'
                );
        }

        $category->restore();

        $category->update([
            'restored_by' => auth()->id(),
            'restored_at' => now(),
        ]);

        return redirect()
            ->route(
                'admin.categories.trash'
            )
            ->with(
                'success',
                'Khôi phục danh mục thành công.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Xóa vĩnh viễn danh mục
    |--------------------------------------------------------------------------
    */

    public function forceDelete(string $id)
    {
        $category = Category::onlyTrashed()
            ->findOrFail($id);

        /*
         * Kiểm tra lại để đảm bảo không còn sản phẩm.
         */
        if (
            $category
                ->products()
                ->withTrashed()
                ->exists()
        ) {
            return redirect()
                ->route(
                    'admin.categories.trash'
                )
                ->with(
                    'error',
                    'Không thể xóa vĩnh viễn danh mục vẫn còn sản phẩm.'
                );
        }

        /*
         * Xóa ảnh thật khi force delete.
         *
         * - Cloudinary -> xóa Cloudinary
         * - local cũ -> xóa storage
         */
        $this->deleteStoredImage(
            $category->image
        );

        $category->forceDelete();

        return redirect()
            ->route(
                'admin.categories.trash'
            )
            ->with(
                'success',
                'Đã xóa vĩnh viễn danh mục.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Validation messages
    |--------------------------------------------------------------------------
    */

    private function messages(): array
    {
        return [
            'name.required' =>
                'Vui lòng nhập tên danh mục.',

            'image.image' =>
                'Ảnh tải lên không hợp lệ.',

            'image.mimes' =>
                'Ảnh chỉ nhận JPG, JPEG, PNG hoặc WEBP.',

            'image.max' =>
                'Ảnh không được vượt quá 2MB.',

            'sort_order.integer' =>
                'Thứ tự hiển thị phải là số nguyên.',

            'sort_order.min' =>
                'Thứ tự hiển thị không được nhỏ hơn 0.',
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
    | Cloudinary instance
    |--------------------------------------------------------------------------
    */

    private function cloudinary(): Cloudinary
    {
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
    | Upload ảnh lên Cloudinary
    |--------------------------------------------------------------------------
    */

    private function uploadToCloudinary(
        UploadedFile $file,
        string $folder
    ): string {
        $result =
            $this->cloudinary()
                ->uploadApi()
                ->upload(
                    $file->getRealPath(),
                    [
                        'folder' =>
                            $folder,

                        'resource_type' =>
                            'image',

                        'use_filename' =>
                            true,

                        'unique_filename' =>
                            true,

                        'overwrite' =>
                            false,
                    ]
                );

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
    | Hỗ trợ:
    | 1. Cloudinary mới
    | 2. Local Laravel cũ
    | 3. URL ngoài
    |--------------------------------------------------------------------------
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
                $this->cloudinary()
                    ->uploadApi()
                    ->destroy(
                        $publicId,
                        [
                            'resource_type' =>
                                'image',

                            'invalidate' =>
                                true,
                        ]
                    );
            } catch (\Throwable $e) {
                /*
                 * Không làm hỏng thao tác DB
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
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Local cũ
        |--------------------------------------------------------------------------
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
    | Lấy public_id từ URL Cloudinary
    |--------------------------------------------------------------------------
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

        $relativePath =
            substr(
                $path,
                $position
                + strlen($marker)
            );

        /*
         * Bỏ version Cloudinary:
         * v1234567890/...
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
         * Bỏ extension:
         * .jpg / .png / .webp...
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

        return rawurldecode(
            ltrim(
                $publicId,
                '/'
            )
        );
    }
}
