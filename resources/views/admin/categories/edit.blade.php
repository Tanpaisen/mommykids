@extends('admin.layouts.app')

@section('page_title', 'Chỉnh sửa sản phẩm')

@section('page_subtitle')
    Cập nhật thông tin "{{ $product->name }}"
@endsection

@section('page_actions')
    <a
        href="{{ route('admin.products.index') }}"
        class="px-4 py-2.5 rounded-xl
               border border-admin-border bg-white
               text-sm text-ink hover:bg-admin-bg"
    >
        ← Quay lại
    </a>
@endsection


@section('content')

<form
    action="{{ route('admin.products.update', $product) }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf
    @method('PUT')


    @php
        $selectedStageIds = collect(
            old(
                'stage_ids',
                $product->stages->pluck('id')->all()
            )
        )->map(fn ($id) => (string) $id)->all();

        $selectedTagIds = collect(
            old(
                'tag_ids',
                $product->tags->pluck('id')->all()
            )
        )->map(fn ($id) => (string) $id)->all();
    @endphp


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
                        Cập nhật thông tin và trạng thái sản phẩm.
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
                            required
                            value="{{ old('name', $product->name) }}"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3
                                   outline-none focus:border-coral"
                        >
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
                                @foreach ($categories as $category)

                                    <option
                                        value="{{ $category->id }}"
                                        @selected(
                                            (string) old(
                                                'category_id',
                                                $product->category_id
                                            )
                                            ===
                                            (string) $category->id
                                        )
                                    >
                                        {{ $category->name }}
                                    </option>

                                @endforeach
                            </select>
                        </div>


                        <div>
                            <label class="block mb-2 text-sm font-semibold">
                                Slug
                            </label>

                            <input
                                type="text"
                                name="slug"
                                value="{{ old('slug', $product->slug) }}"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-3
                                       outline-none focus:border-coral"
                            >
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
                        >{{ old('description', $product->description) }}</textarea>
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
                                Hiển thị sản phẩm phía khách hàng.
                            </p>
                        </div>

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(
                                old(
                                    'is_active',
                                    $product->is_active
                                )
                            )
                            class="w-5 h-5 accent-coral"
                        >
                    </label>

                </div>

            </div>


            {{-- PRICE --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4 mb-5">
                    <h2 class="font-semibold">
                        Giá & Tồn kho
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Giá bán
                        </label>

                        <input
                            type="number"
                            name="price"
                            min="0"
                            required
                            value="{{ old('price', $product->price) }}"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3"
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
                            value="{{ old(
                                'old_price',
                                $product->old_price
                            ) }}"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3"
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
                            value="{{ old(
                                'discount_percent',
                                $product->discount_percent
                            ) }}"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3"
                        >
                    </div>


                    <div>
                        <label class="block mb-2 text-sm font-semibold">
                            Tồn kho
                        </label>

                        <input
                            type="number"
                            name="stock"
                            min="0"
                            required
                            value="{{ old('stock', $product->stock) }}"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-3"
                        >
                    </div>

                </div>

            </div>


            {{-- IMAGE --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4 mb-5">

                    <h2 class="font-semibold text-ink">
                        Hình ảnh sản phẩm
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Ảnh đại diện được sử dụng trên trang chủ
                        và danh sách sản phẩm.
                    </p>

                </div>


                {{-- MAIN IMAGE --}}
                <div>

                    <label class="block mb-3 text-sm font-semibold">
                        Ảnh đại diện
                    </label>


                    @if ($product->image)

                        <div
                            class="w-40 h-40 rounded-2xl
                                   border border-admin-border
                                   overflow-hidden bg-admin-bg mb-3"
                        >

                            <img
                                src="{{ str_starts_with($product->image, 'http')
                                    ? $product->image
                                    : asset('storage/' . $product->image) }}"
                                alt="{{ $product->name }}"
                                class="w-full h-full object-contain bg-white"
                            >

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

                            Xóa ảnh đại diện hiện tại
                        </label>

                    @else

                        <div
                            class="w-40 h-40 rounded-2xl
                                   border border-dashed border-admin-border
                                   bg-admin-bg
                                   flex flex-col items-center justify-center
                                   mb-3 text-ink-soft"
                        >
                            <span class="text-3xl">
                                🖼️
                            </span>

                            <span class="text-xs mt-2">
                                Chưa có ảnh
                            </span>
                        </div>

                    @endif


                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp,image/*"
                        class="w-full border border-admin-border
                               rounded-xl px-4 py-3"
                    >

                    <p class="mt-1.5 text-xs text-ink-soft">
                        Chọn ảnh mới nếu muốn thay ảnh hiện tại.
                    </p>

                </div>


                {{-- GALLERY --}}
                <div class="mt-7 pt-6 border-t border-admin-border">

                    <label class="block mb-3 text-sm font-semibold">
                        Ảnh chi tiết
                    </label>


                    @if (!empty($product->images))

                        <div
                            class="grid grid-cols-2
                                   sm:grid-cols-3
                                   md:grid-cols-4
                                   gap-3 mb-4"
                        >

                            @foreach ($product->images as $image)

                                <div
                                    class="border border-admin-border
                                           rounded-xl p-2 bg-white"
                                >

                                    <img
                                        src="{{ str_starts_with($image, 'http')
                                            ? $image
                                            : asset('storage/' . $image) }}"
                                        alt=""
                                        class="w-full h-24
                                               rounded-lg object-cover"
                                    >

                                    <label
                                        class="flex items-center gap-2
                                               mt-2 text-xs text-red-500
                                               cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            name="remove_gallery[]"
                                            value="{{ $image }}"
                                            class="accent-red-500"
                                        >

                                        Xóa
                                    </label>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <p class="text-sm text-ink-soft mb-3">
                            Chưa có ảnh chi tiết.
                        </p>

                    @endif


                    <input
                        type="file"
                        name="images[]"
                        multiple
                        accept=".jpg,.jpeg,.png,.webp,image/*"
                        class="w-full border border-admin-border
                               rounded-xl px-4 py-3"
                    >

                </div>

            </div>

        </div>


        {{-- RIGHT --}}
        <div class="space-y-6">

            {{-- STAGES --}}
            <div class="card">

                <h2 class="font-semibold">
                    Giai đoạn phù hợp
                </h2>

                <p class="text-sm text-ink-soft mt-1">
                    Chọn một hoặc nhiều giai đoạn.
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
                                        (string) $stage->id,
                                        $selectedStageIds,
                                        true
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


            {{-- TAGS --}}
            <div class="card">

                <h2 class="font-semibold">
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
                                            (string) $tag->id,
                                            $selectedTagIds,
                                            true
                                        )
                                    )
                                    class="accent-coral"
                                >

                                <span class="text-sm font-medium">
                                    {{ $tag->name }}
                                </span>

                            </div>


                            @if ($tag->type === 'brand')

                                <span
                                    class="text-xs bg-amber-50
                                           text-amber-600
                                           rounded-full px-2 py-1"
                                >
                                    Thương hiệu
                                </span>

                            @else

                                <span
                                    class="text-xs bg-blue-50
                                           text-blue-600
                                           rounded-full px-2 py-1"
                                >
                                    {{ $tag->type === 'attribute'
                                        ? 'Thuộc tính'
                                        : 'Giai đoạn' }}
                                </span>

                            @endif

                        </label>

                    @endforeach

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