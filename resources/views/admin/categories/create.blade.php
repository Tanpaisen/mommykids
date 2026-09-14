@extends('admin.layouts.app')

@section('page_title', 'Thêm sản phẩm')
@section('page_subtitle', 'Tạo sản phẩm mới cho MommyKids')

@section('page_actions')
    <a
        href="{{ route('admin.products.index') }}"
        class="px-4 py-2.5 rounded-xl border border-admin-border
               bg-white text-sm text-ink hover:bg-admin-bg transition"
    >
        ← Quay lại
    </a>
@endsection


@section('content')

<form
    action="{{ route('admin.products.store') }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf

    <div class="grid grid-cols-1 xl:grid-cols-[1.55fr_0.95fr] gap-6">

        {{-- LEFT --}}
        <div class="space-y-6">

            {{-- BASIC --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4 mb-5">
                    <h2 class="font-semibold text-ink">
                        Thông tin cơ bản
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Nhập thông tin chính của sản phẩm.
                    </p>
                </div>

                <div class="space-y-5">

                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Tên sản phẩm
                            <span class="text-coral">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3
                                   outline-none focus:border-coral"
                        >

                        @error('name')
                            <p class="mt-1.5 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <label class="block mb-2 text-sm font-semibold">
                                Danh mục
                                <span class="text-coral">*</span>
                            </label>

                            <select
                                name="category_id"
                                required
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-3
                                       outline-none focus:border-coral"
                            >
                                <option value="">
                                    -- Chọn danh mục --
                                </option>

                                @foreach ($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        @selected(
                                            (string) old('category_id')
                                            ===
                                            (string) $category->id
                                        )
                                    >
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('category_id')
                                <p class="mt-1.5 text-xs text-red-500">
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
                                value="{{ old('slug') }}"
                                placeholder="Để trống để tự tạo"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-3
                                       outline-none focus:border-coral"
                            >

                            @error('slug')
                                <p class="mt-1.5 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>


                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Mô tả
                        </label>

                        <textarea
                            name="description"
                            rows="6"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3
                                   outline-none focus:border-coral"
                        >{{ old('description') }}</textarea>
                    </div>


                    <label
                        class="flex items-center justify-between
                               border border-admin-border
                               rounded-xl px-4 py-4"
                    >
                        <div>
                            <p class="font-semibold text-sm">
                                Đang bán
                            </p>

                            <p class="text-xs text-ink-soft mt-1">
                                Hiển thị sản phẩm ở trang khách hàng.
                            </p>
                        </div>

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', true))
                            class="w-5 h-5 accent-coral"
                        >
                    </label>

                </div>

            </div>


            {{-- PRICE --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4 mb-5">
                    <h2 class="font-semibold text-ink">
                        Giá & Tồn kho
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Giá bán
                            <span class="text-coral">*</span>
                        </label>

                        <input
                            type="number"
                            name="price"
                            min="0"
                            required
                            value="{{ old('price') }}"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3
                                   outline-none focus:border-coral"
                        >
                    </div>


                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Giá cũ
                        </label>

                        <input
                            type="number"
                            name="old_price"
                            min="0"
                            value="{{ old('old_price') }}"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3
                                   outline-none focus:border-coral"
                        >
                    </div>


                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Giảm giá %
                        </label>

                        <input
                            type="number"
                            name="discount_percent"
                            min="0"
                            max="100"
                            value="{{ old('discount_percent') }}"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3
                                   outline-none focus:border-coral"
                        >
                    </div>


                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Tồn kho
                            <span class="text-coral">*</span>
                        </label>

                        <input
                            type="number"
                            name="stock"
                            min="0"
                            required
                            value="{{ old('stock', 0) }}"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3
                                   outline-none focus:border-coral"
                        >
                    </div>

                </div>

            </div>


            {{-- IMAGES --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4 mb-5">

                    <h2 class="font-semibold text-ink">
                        Hình ảnh sản phẩm
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Ảnh đại diện sẽ được sử dụng tại trang chủ
                        và danh sách sản phẩm.
                    </p>

                </div>


                <div class="space-y-5">

                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Ảnh đại diện
                        </label>

                        <input
                            type="file"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp,image/*"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3 bg-white"
                        >

                        <p class="mt-1.5 text-xs text-ink-soft">
                            JPG, PNG hoặc WEBP. Tối đa 4MB.
                        </p>

                        @error('image')
                            <p class="mt-1.5 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Ảnh chi tiết
                        </label>

                        <input
                            type="file"
                            name="images[]"
                            multiple
                            accept=".jpg,.jpeg,.png,.webp,image/*"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3 bg-white"
                        >

                        <p class="mt-1.5 text-xs text-ink-soft">
                            Có thể chọn tối đa 8 ảnh.
                        </p>
                    </div>

                </div>

            </div>

        </div>


        {{-- RIGHT --}}
        <div class="space-y-6">

            {{-- STAGES --}}
            <div class="card">

                <h2 class="font-semibold text-ink">
                    Giai đoạn phù hợp
                </h2>

                <p class="text-sm text-ink-soft mt-1">
                    Chọn các giai đoạn sử dụng phù hợp.
                </p>

                <div class="mt-4 space-y-2 max-h-[380px] overflow-y-auto">

                    @foreach ($stages as $stage)

                        <label
                            class="flex items-start gap-3
                                   border border-admin-border
                                   rounded-xl px-4 py-3
                                   cursor-pointer hover:bg-admin-bg"
                        >
                            <input
                                type="checkbox"
                                name="stage_ids[]"
                                value="{{ $stage->id }}"
                                @checked(
                                    in_array(
                                        $stage->id,
                                        old('stage_ids', [])
                                    )
                                )
                                class="mt-1 accent-coral"
                            >

                            <div>
                                <p class="font-semibold text-sm">
                                    {{ $stage->icon }}
                                    {{ $stage->name }}
                                </p>

                                <p class="text-xs text-ink-soft mt-1">
                                    {{ $stage->age_from }}
                                    -
                                    {{ $stage->age_to }}
                                    tháng
                                </p>
                            </div>
                        </label>

                    @endforeach

                </div>

            </div>


            {{-- TAG --}}
            <div class="card">

                <h2 class="font-semibold text-ink">
                    Thuộc tính / Tags
                </h2>

                <div class="mt-4 space-y-2 max-h-[420px] overflow-y-auto">

                    @foreach ($tags as $tag)

                        <label
                            class="flex items-center justify-between
                                   border border-admin-border
                                   rounded-xl px-4 py-3
                                   cursor-pointer hover:bg-admin-bg"
                        >

                            <div class="flex items-center gap-3">

                                <input
                                    type="checkbox"
                                    name="tag_ids[]"
                                    value="{{ $tag->id }}"
                                    @checked(
                                        in_array(
                                            $tag->id,
                                            old('tag_ids', [])
                                        )
                                    )
                                    class="accent-coral"
                                >

                                <span class="text-sm font-medium">
                                    {{ $tag->name }}
                                </span>

                            </div>

                            <span class="text-xs text-ink-soft">
                                {{ $tag->type }}
                            </span>

                        </label>

                    @endforeach

                </div>

            </div>


            <div class="card">

                <button
                    type="submit"
                    class="btn-primary w-full"
                >
                    + Thêm sản phẩm
                </button>

                <a
                    href="{{ route('admin.products.index') }}"
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

@endsection