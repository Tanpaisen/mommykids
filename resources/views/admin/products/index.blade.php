@extends('admin.layouts.app')

@section('page_title', 'Sản phẩm')
@section('page_subtitle', 'Quản lý sản phẩm, hình ảnh, giá và tồn kho')

@section('page_actions')
    <a
        href="{{ route('admin.products.create') }}"
        class="btn-primary"
    >
        + Thêm sản phẩm
    </a>
@endsection


@section('content')

{{-- FILTER --}}
<div class="card mb-5">

    <form
        method="GET"
        action="{{ route('admin.products.index') }}"
        class="grid grid-cols-1
               md:grid-cols-2
               xl:grid-cols-[2fr_1fr_1fr_auto_auto]
               gap-3"
    >

        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Tìm theo tên hoặc slug sản phẩm..."
            class="border border-admin-border
                   rounded-xl px-4 py-3
                   outline-none focus:border-coral"
        >


        <select
            name="category_id"
            class="border border-admin-border
                   rounded-xl px-4 py-3
                   outline-none focus:border-coral"
        >
            <option value="">
                Tất cả danh mục
            </option>

            @foreach ($categories as $category)

                <option
                    value="{{ $category->id }}"
                    @selected(
                        (string) request('category_id')
                        ===
                        (string) $category->id
                    )
                >
                    {{ $category->name }}
                </option>

            @endforeach
        </select>


        <select
            name="status"
            class="border border-admin-border
                   rounded-xl px-4 py-3
                   outline-none focus:border-coral"
        >
            <option value="">
                Tất cả trạng thái
            </option>

            <option
                value="active"
                @selected(request('status') === 'active')
            >
                Đang bán
            </option>

            <option
                value="inactive"
                @selected(request('status') === 'inactive')
            >
                Đã ẩn
            </option>
        </select>


        <label
            class="flex items-center justify-center gap-2
                   border border-admin-border
                   rounded-xl px-4 py-3
                   cursor-pointer whitespace-nowrap"
        >
            <input
                type="checkbox"
                name="low_stock"
                value="1"
                @checked(request()->boolean('low_stock'))
                class="accent-coral"
            >

            Tồn kho thấp
        </label>


        <button
            type="submit"
            class="bg-coral text-white
                   rounded-xl px-6 py-3
                   font-semibold"
        >
            Lọc
        </button>

    </form>

</div>



{{-- TABLE --}}
<div class="card overflow-hidden">

    <div class="overflow-x-auto">

        <table class="w-full min-w-[1100px] text-sm">

            <thead
                class="bg-admin-bg
                       text-xs uppercase
                       text-ink-soft"
            >

                <tr>
                    <th class="text-left px-5 py-4">
                        STT
                    </th>

                    <th class="text-left px-5 py-4 min-w-[340px]">
                        Sản phẩm
                    </th>

                    <th class="text-left px-5 py-4">
                        Danh mục
                    </th>

                    <th class="text-left px-5 py-4">
                        Giá
                    </th>

                    <th class="text-center px-5 py-4">
                        Tồn kho
                    </th>

                    <th class="text-left px-5 py-4">
                        Trạng thái
                    </th>

                    <th class="text-right px-5 py-4">
                        Thao tác
                    </th>
                </tr>

            </thead>


            <tbody class="divide-y divide-admin-border">

                @forelse ($products as $product)

                    <tr class="hover:bg-admin-bg/40 transition">

                        <td class="px-5 py-4 text-ink-soft">
                            {{ $products->firstItem() + $loop->index }}
                        </td>


                        {{-- PRODUCT --}}
                        <td class="px-5 py-4">

                            <div class="flex items-center gap-4">

                                <div
                                    class="w-16 h-16
                                           shrink-0
                                           rounded-xl
                                           border border-admin-border
                                           overflow-hidden
                                           bg-white
                                           flex items-center justify-center"
                                >

                                    @if ($product->image)

                                        <img
                                            src="{{ str_starts_with($product->image, 'http')
                                                ? $product->image
                                                : asset('storage/' . $product->image) }}"
                                            alt="{{ $product->name }}"
                                            class="w-full h-full object-contain"
                                            onerror="
                                                this.style.display='none';
                                                this.nextElementSibling.style.display='flex';
                                            "
                                        >

                                        <span
                                            style="display:none;"
                                            class="w-full h-full
                                                   items-center justify-center
                                                   text-2xl text-ink-soft"
                                        >
                                            🖼️
                                        </span>

                                    @else

                                        <span class="text-2xl text-ink-soft">
                                            🖼️
                                        </span>

                                    @endif

                                </div>


                                <div class="min-w-0">

                                    <p class="font-semibold text-ink">
                                        {{ $product->name }}
                                    </p>

                                    <p
                                        class="text-xs text-ink-soft
                                               truncate max-w-[280px] mt-1"
                                        title="{{ $product->slug }}"
                                    >
                                        {{ $product->slug }}
                                    </p>

                                    <p class="text-xs text-ink-soft mt-1">
                                        ID #{{ $product->id }}
                                    </p>

                                </div>

                            </div>

                        </td>


                        {{-- CATEGORY --}}
                        <td class="px-5 py-4">

                            <span
                                class="inline-flex
                                       bg-admin-bg rounded-lg
                                       px-2.5 py-1 text-xs"
                            >
                                {{ $product->category?->name ?? 'Chưa phân loại' }}
                            </span>

                        </td>


                        {{-- PRICE --}}
                        <td class="px-5 py-4">

                            <p class="font-semibold text-coral">
                                {{ number_format(
                                    $product->price,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </p>

                            @if ($product->old_price)

                                <p
                                    class="text-xs text-ink-soft
                                           line-through mt-1"
                                >
                                    {{ number_format(
                                        $product->old_price,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </p>

                            @endif

                            @if ($product->discount_percent)

                                <span
                                    class="inline-flex mt-1
                                           bg-red-50
                                           text-red-500
                                           rounded-full
                                           px-2 py-0.5
                                           text-xs"
                                >
                                    -{{ $product->discount_percent }}%
                                </span>

                            @endif

                        </td>


                        {{-- STOCK --}}
                        <td class="px-5 py-4 text-center">

                            <span
                                class="inline-flex min-w-10 h-9
                                       items-center justify-center
                                       rounded-xl px-2
                                       {{ $product->stock <= 10
                                            ? 'bg-red-50 text-red-500'
                                            : 'bg-admin-bg text-ink' }}
                                       font-semibold"
                            >
                                {{ $product->stock }}
                            </span>

                        </td>


                        {{-- STATUS --}}
                        <td class="px-5 py-4">

                            @if ($product->is_active)

                                <span
                                    class="inline-flex items-center gap-2
                                           rounded-full bg-green-50
                                           text-green-600
                                           px-3 py-1.5
                                           text-xs font-semibold"
                                >
                                    <span
                                        class="w-2 h-2
                                               rounded-full bg-green-500"
                                    ></span>

                                    Đang bán
                                </span>

                            @else

                                <span
                                    class="inline-flex items-center gap-2
                                           rounded-full bg-gray-100
                                           text-gray-500
                                           px-3 py-1.5
                                           text-xs font-semibold"
                                >
                                    <span
                                        class="w-2 h-2
                                               rounded-full bg-gray-400"
                                    ></span>

                                    Đã ẩn
                                </span>

                            @endif

                        </td>


                        {{-- ACTION --}}
                        <td class="px-5 py-4">

                            <div class="flex justify-end gap-2">

                                <a
                                    href="{{ route(
                                        'admin.products.edit',
                                        $product
                                    ) }}"
                                    class="px-4 py-2
                                           border border-admin-border
                                           rounded-lg
                                           hover:border-coral"
                                >
                                    Sửa
                                </a>


                                <button
                                    type="button"
                                    data-action="{{ route(
                                        'admin.products.destroy',
                                        $product
                                    ) }}"
                                    data-name="{{ $product->name }}"
                                    onclick="openDeleteProductModal(this)"
                                    class="px-4 py-2
                                           bg-red-50
                                           text-red-500
                                           rounded-lg
                                           hover:bg-red-100"
                                >
                                    Xóa
                                </button>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="py-20 text-center">

                            <div class="text-4xl">
                                🛍️
                            </div>

                            <p class="font-semibold mt-3">
                                Không tìm thấy sản phẩm
                            </p>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>



    {{-- PAGINATION --}}
    @if ($products->total() > 0)

        <div
            class="flex flex-col sm:flex-row
                   sm:items-center
                   sm:justify-between
                   gap-4
                   border-t border-admin-border
                   px-5 py-4"
        >

            <p class="text-sm text-ink-soft">

                Hiển thị

                <strong>
                    {{ $products->firstItem() }}
                </strong>

                -

                <strong>
                    {{ $products->lastItem() }}
                </strong>

                trong

                <strong>
                    {{ $products->total() }}
                </strong>

                sản phẩm

            </p>


            @if ($products->hasPages())

                @php
                    $current = $products->currentPage();
                    $last = $products->lastPage();

                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);

                    if ($current <= 3) {
                        $end = min(5, $last);
                    }

                    if ($current >= $last - 2) {
                        $start = max(1, $last - 4);
                    }
                @endphp


                <div class="flex items-center gap-1">

                    {{-- PREVIOUS --}}
                    @if ($products->onFirstPage())

                        <span
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   text-gray-300"
                        >
                            ‹
                        </span>

                    @else

                        <a
                            href="{{ $products->previousPageUrl() }}"
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   hover:border-coral"
                        >
                            ‹
                        </a>

                    @endif


                    {{-- FIRST --}}
                    @if ($start > 1)

                        <a
                            href="{{ $products->url(1) }}"
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center"
                        >
                            1
                        </a>

                        @if ($start > 2)
                            <span class="px-2">
                                ...
                            </span>
                        @endif

                    @endif


                    {{-- PAGE WINDOW --}}
                    @for ($page = $start; $page <= $end; $page++)

                        @if ($page === $current)

                            <span
                                class="w-10 h-10
                                       bg-coral text-white
                                       rounded-xl
                                       flex items-center justify-center
                                       font-semibold"
                            >
                                {{ $page }}
                            </span>

                        @else

                            <a
                                href="{{ $products->url($page) }}"
                                class="w-10 h-10
                                       border border-admin-border
                                       rounded-xl
                                       flex items-center justify-center
                                       hover:border-coral"
                            >
                                {{ $page }}
                            </a>

                        @endif

                    @endfor


                    {{-- LAST --}}
                    @if ($end < $last)

                        @if ($end < $last - 1)
                            <span class="px-2">
                                ...
                            </span>
                        @endif

                        <a
                            href="{{ $products->url($last) }}"
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center"
                        >
                            {{ $last }}
                        </a>

                    @endif


                    {{-- NEXT --}}
                    @if ($products->hasMorePages())

                        <a
                            href="{{ $products->nextPageUrl() }}"
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   hover:border-coral"
                        >
                            ›
                        </a>

                    @else

                        <span
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   text-gray-300"
                        >
                            ›
                        </span>

                    @endif

                </div>

            @endif

        </div>

    @endif

</div>



{{-- DELETE MODAL --}}
<div
    id="deleteProductModal"
    class="fixed inset-0 z-50
           hidden items-center justify-center
           bg-black/40 px-4"
>

    <div
        class="w-full max-w-md
               bg-white rounded-2xl
               shadow-2xl p-6"
    >

        <h3 class="text-lg font-semibold">
            Xóa sản phẩm?
        </h3>

        <p class="text-sm text-ink-soft mt-2">
            Bạn có chắc muốn xóa
            <strong
                id="deleteProductName"
                class="text-ink"
            ></strong>?
        </p>

        <p class="text-xs text-red-500 mt-2">
            Thao tác này không thể hoàn tác.
        </p>


        <div class="flex justify-end gap-3 mt-6">

            <button
                type="button"
                onclick="closeDeleteProductModal()"
                class="border border-admin-border
                       rounded-xl px-4 py-2.5"
            >
                Hủy
            </button>


            <form
                id="deleteProductForm"
                method="POST"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="bg-red-500
                           text-white
                           rounded-xl
                           px-4 py-2.5"
                >
                    Xóa sản phẩm
                </button>
            </form>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>
    function openDeleteProductModal(button) {
        const modal = document.getElementById(
            'deleteProductModal'
        );

        document.getElementById(
            'deleteProductForm'
        ).action = button.dataset.action;

        document.getElementById(
            'deleteProductName'
        ).textContent = button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }

    function closeDeleteProductModal() {
        const modal = document.getElementById(
            'deleteProductModal'
        );

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }
</script>

@endpush