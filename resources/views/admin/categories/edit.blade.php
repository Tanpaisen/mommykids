@extends('admin.layouts.app')

@section('page_title', 'Chỉnh sửa danh mục')

@section('page_subtitle')
    Cập nhật thông tin danh mục "{{ $category->name }}"
@endsection

@section('page_actions')
    <a
        href="{{ route('admin.categories.index') }}"
        class="px-4 py-2.5 rounded-xl
               border border-admin-border bg-white
               text-sm text-ink hover:bg-admin-bg"
    >
        ← Quay lại
    </a>
@endsection


@section('content')

<form
    action="{{ route('admin.categories.update', $category) }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf
    @method('PUT')

    @php
        $categoryImage = null;

        if ($category->image) {
            $categoryImage =
                str_starts_with($category->image, 'http://')
                || str_starts_with($category->image, 'https://')
                    ? $category->image
                    : asset(
                        'storage/' .
                        ltrim(
                            $category->image,
                            '/'
                        )
                    );
        }
    @endphp


    <div class="grid grid-cols-1 xl:grid-cols-[1.55fr_0.95fr] gap-6">

        {{-- LEFT --}}
        <div class="space-y-6">

            {{-- THÔNG TIN CƠ BẢN --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4 mb-5">
                    <h2 class="font-semibold text-ink">
                        Thông tin cơ bản
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Cập nhật tên, slug, biểu tượng và thứ tự hiển thị.
                    </p>
                </div>


                <div class="space-y-5">

                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Tên danh mục
                            <span class="text-coral">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            required
                            value="{{ old('name', $category->name) }}"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3
                                   outline-none focus:border-coral"
                        >

                        @error('name')
                            <p class="mt-1.5 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Slug
                        </label>

                        <input
                            type="text"
                            name="slug"
                            value="{{ old('slug', $category->slug) }}"
                            placeholder="Ví dụ: sua-cho-be"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3
                                   outline-none focus:border-coral"
                        >

                        <p class="mt-1.5 text-xs text-ink-soft">
                            Có thể để trống để hệ thống tự tạo slug từ tên danh mục.
                        </p>

                        @error('slug')
                            <p class="mt-1.5 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <label class="block mb-2 text-sm font-semibold">
                                Icon dự phòng
                            </label>

                            <input
                                type="text"
                                name="icon"
                                value="{{ old('icon', $category->icon) }}"
                                placeholder="Ví dụ: 🍼"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-3
                                       outline-none focus:border-coral"
                            >

                            <p class="mt-1.5 text-xs text-ink-soft">
                                Dùng khi danh mục chưa có ảnh/icon Cloudinary.
                            </p>

                            @error('icon')
                                <p class="mt-1.5 text-sm text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>


                        <div>
                            <label class="block mb-2 text-sm font-semibold">
                                Thứ tự hiển thị
                            </label>

                            <input
                                type="number"
                                name="sort_order"
                                min="0"
                                value="{{ old(
                                    'sort_order',
                                    $category->sort_order ?? 0
                                ) }}"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-3
                                       outline-none focus:border-coral"
                            >

                            @error('sort_order')
                                <p class="mt-1.5 text-sm text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>


                    <label
                        class="flex items-center justify-between
                               border border-admin-border
                               rounded-xl px-4 py-4"
                    >
                        <div>
                            <p class="font-semibold text-sm">
                                Hiển thị danh mục
                            </p>

                            <p class="text-xs text-ink-soft mt-1">
                                Danh mục sẽ xuất hiện ở phía khách hàng.
                            </p>
                        </div>

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(
                                old(
                                    'is_active',
                                    $category->is_active
                                )
                            )
                            class="w-5 h-5 accent-coral"
                        >
                    </label>

                </div>

            </div>


            {{-- ẢNH / ICON DANH MỤC --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4 mb-5">
                    <h2 class="font-semibold text-ink">
                        Ảnh / Icon danh mục
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Chọn PNG, JPG hoặc WEBP. Ảnh mới sẽ được tải thẳng lên Cloudinary.
                    </p>
                </div>


                <div class="space-y-5">

                    <div>
                        <label class="block mb-3 text-sm font-semibold">
                            Icon hiện tại
                        </label>


                        @if($categoryImage)

                            <div
                                class="w-32 h-32 rounded-2xl
                                       border border-admin-border
                                       overflow-hidden bg-pink-50
                                       flex items-center justify-center
                                       mb-3"
                            >
                                <img
                                    src="{{ $categoryImage }}"
                                    alt="{{ $category->name }}"
                                    class="w-full h-full object-contain bg-white p-2"
                                    onerror="
                                        this.style.display='none';
                                        this.nextElementSibling.style.display='flex';
                                    "
                                >

                                <div
                                    style="display:none;"
                                    class="w-full h-full
                                           items-center justify-center
                                           text-4xl"
                                >
                                    {{ $category->icon ?: '📦' }}
                                </div>
                            </div>


                            <label
                                class="inline-flex items-center gap-2
                                       text-sm text-red-500
                                       cursor-pointer mb-4"
                            >
                                <input
                                    type="checkbox"
                                    name="remove_image"
                                    value="1"
                                    class="accent-red-500"
                                >

                                Xóa icon ảnh hiện tại
                            </label>

                        @else

                            <div
                                class="w-32 h-32 rounded-2xl
                                       border border-dashed border-admin-border
                                       bg-pink-50
                                       flex flex-col items-center justify-center
                                       mb-3"
                            >
                                <span class="text-4xl">
                                    {{ $category->icon ?: '📦' }}
                                </span>

                                <span class="text-xs text-ink-soft mt-2">
                                    Chưa có ảnh Cloudinary
                                </span>
                            </div>

                        @endif
                    </div>


                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Chọn icon ảnh mới
                        </label>

                        <input
                            id="category-image-input"
                            type="file"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp,image/*"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3 bg-white"
                        >

                        <p class="mt-1.5 text-xs text-ink-soft">
                            Tối đa 2MB. Khuyên dùng PNG/WebP vuông để hiển thị đẹp trên menu.
                        </p>

                        @error('image')
                            <p class="mt-1.5 text-sm text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

            </div>

        </div>


        {{-- RIGHT --}}
        <div class="space-y-6">

            {{-- XEM TRƯỚC --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4 mb-5">
                    <h2 class="font-semibold text-ink">
                        Xem trước
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Minh họa danh mục trên menu khách hàng.
                    </p>
                </div>


                <div
                    class="flex items-center gap-4
                           border border-admin-border
                           rounded-2xl p-4 bg-white"
                >

                    <div
                        class="w-16 h-16 shrink-0
                               rounded-full bg-pink-50
                               flex items-center justify-center
                               overflow-hidden"
                    >
                        <img
                            id="category-menu-preview-image"
                            src="{{ $categoryImage ?? '' }}"
                            alt="{{ $category->name }}"
                            class="w-full h-full object-contain p-1
                                   {{ $categoryImage ? '' : 'hidden' }}"
                        >

                        <span
                            id="category-menu-preview-fallback"
                            class="text-3xl {{ $categoryImage ? 'hidden' : '' }}"
                        >
                            {{ $category->icon ?: '📦' }}
                        </span>
                    </div>


                    <div class="min-w-0 flex-1">

                        <p class="font-semibold text-ink">
                            {{ old(
                                'name',
                                $category->name
                            ) }}
                        </p>

                        <p class="text-xs text-ink-soft mt-1">
                            /{{ old(
                                'slug',
                                $category->slug
                            ) }}
                        </p>

                    </div>

                    <span class="text-ink-soft text-xl">
                        ›
                    </span>

                </div>

            </div>


            {{-- THÔNG TIN HIỆN TẠI --}}
            <div class="card">

                <h2 class="font-semibold text-ink">
                    Thông tin hiện tại
                </h2>


                <div class="mt-4 space-y-3 text-sm">

                    <div
                        class="flex justify-between gap-4
                               border-b border-admin-border pb-3"
                    >
                        <span class="text-ink-soft">
                            ID
                        </span>

                        <span class="font-semibold">
                            #{{ $category->id }}
                        </span>
                    </div>


                    <div
                        class="flex justify-between gap-4
                               border-b border-admin-border pb-3"
                    >
                        <span class="text-ink-soft">
                            Slug
                        </span>

                        <span class="font-medium text-right break-all">
                            {{ $category->slug }}
                        </span>
                    </div>


                    <div
                        class="flex justify-between gap-4
                               border-b border-admin-border pb-3"
                    >
                        <span class="text-ink-soft">
                            Thứ tự
                        </span>

                        <span class="font-semibold">
                            {{ $category->sort_order ?? 0 }}
                        </span>
                    </div>


                    <div
                        class="flex justify-between gap-4
                               border-b border-admin-border pb-3"
                    >
                        <span class="text-ink-soft">
                            Loại icon
                        </span>

                        <span
                            id="category-icon-type"
                            class="font-medium text-right"
                        >
                            {{ $categoryImage
                                ? 'Ảnh / Cloudinary'
                                : 'Emoji dự phòng' }}
                        </span>
                    </div>


                    <div class="flex justify-between gap-4">

                        <span class="text-ink-soft">
                            Trạng thái
                        </span>

                        @if($category->is_active)

                            <span
                                class="inline-flex items-center
                                       px-2.5 py-1 rounded-full
                                       bg-green-50 text-green-600
                                       text-xs font-semibold"
                            >
                                ● Đang hiển thị
                            </span>

                        @else

                            <span
                                class="inline-flex items-center
                                       px-2.5 py-1 rounded-full
                                       bg-gray-100 text-gray-500
                                       text-xs font-semibold"
                            >
                                ● Đang ẩn
                            </span>

                        @endif

                    </div>

                </div>

            </div>


            {{-- ACTION --}}
            <div class="card">

                <button
                    type="submit"
                    class="btn-primary w-full"
                >
                    Lưu thay đổi
                </button>

                <a
                    href="{{ route('admin.categories.index') }}"
                    class="block text-center mt-3
                           border border-admin-border
                           rounded-xl px-4 py-3
                           hover:bg-admin-bg"
                >
                    Hủy
                </a>

            </div>

        </div>

    </div>

</form>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const input =
        document.getElementById(
            'category-image-input'
        );

    const previewImage =
        document.getElementById(
            'category-menu-preview-image'
        );

    const fallback =
        document.getElementById(
            'category-menu-preview-fallback'
        );

    const iconType =
        document.getElementById(
            'category-icon-type'
        );

    if (!input || !previewImage || !fallback) {
        return;
    }

    input.addEventListener('change', function () {

        const file =
            this.files?.[0];

        if (!file) {
            return;
        }

        const url =
            URL.createObjectURL(file);

        previewImage.src = url;
        previewImage.classList.remove('hidden');
        fallback.classList.add('hidden');

        if (iconType) {
            iconType.textContent = 'Ảnh mới (chưa lưu)';
        }
    });

});
</script>

@endsection
