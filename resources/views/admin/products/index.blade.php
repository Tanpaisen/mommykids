@extends('admin.layouts.app')

@section('page_title', 'Sản phẩm')
@section('page_subtitle', 'Quản lý sản phẩm, hình ảnh, giá và tồn kho')


@section('content')

{{-- =====================================================
    HEADER ACTIONS
====================================================== --}}
<div
    class="flex flex-col sm:flex-row
           sm:items-center sm:justify-between
           gap-4 mb-5"
>

    <div>
        <h2 class="text-lg font-semibold text-ink">
            Danh sách sản phẩm
        </h2>

        <p class="text-sm text-ink-soft mt-1">
            Quản lý các sản phẩm đang hoạt động trong hệ thống.
        </p>
    </div>


    <div class="flex flex-wrap items-center gap-3">

        {{-- THÙNG RÁC SẢN PHẨM --}}
        <a
            href="{{ route('admin.products.trash') }}"
            class="inline-flex items-center gap-2
                   h-11 px-4
                   rounded-xl
                   border border-admin-border
                   bg-white
                   text-sm font-medium text-ink
                   hover:border-red-300
                   hover:text-red-500
                   transition"
        >
            <span class="text-base">
                🗑️
            </span>

            <span>
                Thùng rác
            </span>

            @if (($trashCount ?? 0) > 0)
                <span
                    class="inline-flex
                           min-w-5 h-5
                           items-center justify-center
                           rounded-full
                           bg-red-500
                           px-1.5
                           text-[11px]
                           font-semibold
                           text-white"
                >
                    {{ $trashCount }}
                </span>
            @endif
        </a>


        {{-- THÊM SẢN PHẨM --}}
        <a
            href="{{ route('admin.products.create') }}"
            class="inline-flex items-center justify-center gap-2
                   h-11 px-5
                   rounded-xl
                   bg-coral
                   text-white
                   text-sm font-semibold
                   hover:opacity-90
                   transition"
        >
            <span>+</span>

            <span>
                Thêm sản phẩm
            </span>
        </a>

    </div>

</div>



{{-- =====================================================
    FILTER
====================================================== --}}
<div class="card mb-5">

    <form
        method="GET"
        action="{{ route('admin.products.index') }}"
        class="grid grid-cols-1
               md:grid-cols-2
               xl:grid-cols-[2fr_1fr_1fr_auto_auto]
               gap-3"
    >

        {{-- SEARCH --}}
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Tìm theo tên hoặc slug sản phẩm..."
            class="border border-admin-border
                   rounded-xl px-4 py-3
                   bg-white
                   outline-none
                   focus:border-coral
                   focus:ring-2
                   focus:ring-coral/10"
        >


        {{-- CATEGORY --}}
        <select
            name="category_id"
            class="border border-admin-border
                   rounded-xl px-4 py-3
                   bg-white
                   outline-none
                   focus:border-coral
                   focus:ring-2
                   focus:ring-coral/10"
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


        {{-- STATUS --}}
        <select
            name="status"
            class="border border-admin-border
                   rounded-xl px-4 py-3
                   bg-white
                   outline-none
                   focus:border-coral
                   focus:ring-2
                   focus:ring-coral/10"
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


        {{-- LOW STOCK --}}
        <label
            class="flex items-center justify-center gap-2
                   border border-admin-border
                   rounded-xl px-4 py-3
                   bg-white
                   cursor-pointer
                   whitespace-nowrap"
        >
            <input
                type="checkbox"
                name="low_stock"
                value="1"
                @checked(request()->boolean('low_stock'))
                class="accent-coral"
            >

            <span>
                Tồn kho thấp
            </span>
        </label>


        {{-- FILTER BUTTON --}}
        <button
            type="submit"
            class="bg-coral
                   text-white
                   rounded-xl
                   px-6 py-3
                   font-semibold
                   hover:opacity-90
                   transition"
        >
            Lọc
        </button>

    </form>


    {{-- RESET FILTER --}}
    @if (
        request()->filled('search')
        || request()->filled('category_id')
        || request()->filled('status')
        || request()->boolean('low_stock')
    )

        <div class="mt-3">

            <a
                href="{{ route('admin.products.index') }}"
                class="inline-flex items-center gap-1
                       text-sm text-ink-soft
                       hover:text-coral
                       transition"
            >
                ↻ Làm mới bộ lọc
            </a>

        </div>

    @endif

</div>



{{-- =====================================================
    TABLE
====================================================== --}}
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

                        {{-- STT --}}
                        <td class="px-5 py-4 text-ink-soft">
                            {{ $products->firstItem() + $loop->index }}
                        </td>


                        {{-- PRODUCT --}}
                        <td class="px-5 py-4">

                            <div class="flex items-center gap-4">

                                {{-- IMAGE --}}
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


                                {{-- INFO --}}
                                <div class="min-w-0">

                                    <p class="font-semibold text-ink">
                                        {{ $product->name }}
                                    </p>

                                    <p
                                        class="text-xs text-ink-soft
                                               truncate
                                               max-w-[280px]
                                               mt-1"
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
                                       bg-admin-bg
                                       rounded-lg
                                       px-2.5 py-1
                                       text-xs"
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
                                           line-through
                                           mt-1"
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
                                    class="inline-flex
                                           mt-1
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
                                class="inline-flex
                                       min-w-10 h-9
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
                                    class="inline-flex
                                           items-center gap-2
                                           rounded-full
                                           bg-green-50
                                           text-green-600
                                           px-3 py-1.5
                                           text-xs
                                           font-semibold"
                                >

                                    <span
                                        class="w-2 h-2
                                               rounded-full
                                               bg-green-500"
                                    ></span>

                                    Đang bán

                                </span>

                            @else

                                <span
                                    class="inline-flex
                                           items-center gap-2
                                           rounded-full
                                           bg-gray-100
                                           text-gray-500
                                           px-3 py-1.5
                                           text-xs
                                           font-semibold"
                                >

                                    <span
                                        class="w-2 h-2
                                               rounded-full
                                               bg-gray-400"
                                    ></span>

                                    Đã ẩn

                                </span>

                            @endif

                        </td>


                        {{-- ACTION --}}
                        <td class="px-5 py-4">

                            <div class="flex justify-end gap-2">

                                {{-- EDIT --}}
                                <a
                                    href="{{ route(
                                        'admin.products.edit',
                                        $product
                                    ) }}"
                                    class="px-4 py-2
                                           border border-admin-border
                                           rounded-lg
                                           text-ink
                                           hover:border-coral
                                           hover:text-coral
                                           transition"
                                >
                                    Sửa
                                </a>


                                {{-- SOFT DELETE --}}
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
                                           hover:bg-red-100
                                           transition"
                                >
                                    Xóa
                                </button>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="7"
                            class="py-20 text-center"
                        >

                            <div class="text-4xl">
                                🛍️
                            </div>

                            <p class="font-semibold mt-3 text-ink">
                                Không tìm thấy sản phẩm
                            </p>

                            <p class="text-sm text-ink-soft mt-1">
                                Không có sản phẩm phù hợp với bộ lọc hiện tại.
                            </p>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>



    {{-- =====================================================
        PAGINATION
    ====================================================== --}}
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

                <strong class="text-ink">
                    {{ $products->firstItem() }}
                </strong>

                -

                <strong class="text-ink">
                    {{ $products->lastItem() }}
                </strong>

                trong

                <strong class="text-ink">
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
                                   hover:border-coral
                                   hover:text-coral
                                   transition"
                        >
                            ‹
                        </a>

                    @endif


                    {{-- FIRST PAGE --}}
                    @if ($start > 1)

                        <a
                            href="{{ $products->url(1) }}"
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   hover:border-coral
                                   hover:text-coral
                                   transition"
                        >
                            1
                        </a>

                        @if ($start > 2)

                            <span class="px-2 text-ink-soft">
                                ...
                            </span>

                        @endif

                    @endif


                    {{-- PAGE WINDOW --}}
                    @for ($page = $start; $page <= $end; $page++)

                        @if ($page === $current)

                            <span
                                class="w-10 h-10
                                       bg-coral
                                       text-white
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
                                       hover:border-coral
                                       hover:text-coral
                                       transition"
                            >
                                {{ $page }}
                            </a>

                        @endif

                    @endfor


                    {{-- LAST PAGE --}}
                    @if ($end < $last)

                        @if ($end < $last - 1)

                            <span class="px-2 text-ink-soft">
                                ...
                            </span>

                        @endif


                        <a
                            href="{{ $products->url($last) }}"
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   hover:border-coral
                                   hover:text-coral
                                   transition"
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
                                   hover:border-coral
                                   hover:text-coral
                                   transition"
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



{{-- =====================================================
    SOFT DELETE MODAL
====================================================== --}}
<div
    id="deleteProductModal"
    class="fixed inset-0 z-50
           hidden items-center justify-center
           bg-black/40 px-4"
    onclick="closeDeleteProductModalOnBackdrop(event)"
>

    <div
        class="w-full max-w-md
               bg-white
               rounded-2xl
               shadow-2xl
               p-6"
        onclick="event.stopPropagation()"
    >

        <div class="flex items-start gap-4">

            <div
                class="w-12 h-12
                       shrink-0
                       rounded-full
                       bg-red-50
                       text-red-500
                       flex items-center justify-center
                       text-xl"
            >
                🗑️
            </div>


            <div class="flex-1">

                <h3 class="text-lg font-semibold text-ink">
                    Chuyển vào thùng rác?
                </h3>

                <p class="text-sm text-ink-soft mt-2 leading-6">

                    Bạn có chắc muốn chuyển

                    <strong
                        id="deleteProductName"
                        class="text-ink"
                    ></strong>

                    vào thùng rác?

                </p>

                <p class="text-xs text-ink-soft mt-2">
                    Sản phẩm sẽ không còn hiển thị ở danh sách
                    và phía khách hàng, nhưng vẫn có thể khôi phục sau này.
                </p>

            </div>

        </div>


        <div class="flex justify-end gap-3 mt-6">

            <button
                type="button"
                onclick="closeDeleteProductModal()"
                class="border border-admin-border
                       rounded-xl
                       px-4 py-2.5
                       text-sm text-ink
                       hover:bg-admin-bg
                       transition"
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
                           px-4 py-2.5
                           text-sm font-semibold
                           hover:bg-red-600
                           transition"
                >
                    Chuyển vào thùng rác
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

        const form = document.getElementById(
            'deleteProductForm'
        );

        const name = document.getElementById(
            'deleteProductName'
        );

        if (!modal || !form || !name) {
            return;
        }

        form.action = button.dataset.action;
        name.textContent = button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }


    function closeDeleteProductModal() {
        const modal = document.getElementById(
            'deleteProductModal'
        );

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }


    function closeDeleteProductModalOnBackdrop(event) {
        if (
            event.target.id === 'deleteProductModal'
        ) {
            closeDeleteProductModal();
        }
    }


    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeDeleteProductModal();
            }
        }
    );
</script>

@endpush