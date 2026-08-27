@extends('admin.layouts.app')

@section('page_title', 'Sản phẩm')
@section('page_subtitle', 'Quản lý toàn bộ sản phẩm, tồn kho và trạng thái bán')

@section('page_actions')
    <a href="{{ route('admin.products.create') }}" class="btn-primary">
        + Thêm sản phẩm
    </a>
@endsection

@section('content')

    {{-- =====================================================
        FILTER
    ====================================================== --}}
    <div class="card mb-5">

        <form
            action="{{ route('admin.products.index') }}"
            method="GET"
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3"
        >

            {{-- Search --}}
            <div class="xl:col-span-2">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Tìm theo tên hoặc slug sản phẩm..."
                    class="w-full border border-admin-border rounded-xl
                           px-4 py-2.5 text-sm text-ink
                           outline-none focus:border-coral"
                >

            </div>


            {{-- Category --}}
            <div>

                <select
                    name="category_id"
                    class="w-full border border-admin-border rounded-xl
                           px-4 py-2.5 text-sm text-ink
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

            </div>


            {{-- Status --}}
            <div>

                <select
                    name="status"
                    class="w-full border border-admin-border rounded-xl
                           px-4 py-2.5 text-sm text-ink
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

            </div>


            {{-- Low stock + filter --}}
            <div class="flex gap-2">

                <label
                    class="flex flex-1 items-center justify-center gap-2
                           border border-admin-border rounded-xl
                           px-3 py-2.5 text-sm
                           cursor-pointer whitespace-nowrap
                           hover:bg-admin-bg transition"
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
                    class="px-5 py-2.5 rounded-xl
                           bg-coral text-white
                           text-sm font-semibold
                           hover:opacity-90 transition"
                >
                    Lọc
                </button>

            </div>

        </form>


        @if (
            request()->filled('search')
            || request()->filled('category_id')
            || request()->filled('status')
            || request()->boolean('low_stock')
        )

            <div class="mt-3">

                <a
                    href="{{ route('admin.products.index') }}"
                    class="text-sm text-ink-soft hover:text-coral transition"
                >
                    ↻ Làm mới bộ lọc
                </a>

            </div>

        @endif

    </div>



    {{-- =====================================================
        PRODUCT TABLE
    ====================================================== --}}
    <div class="card overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full min-w-[1100px] text-sm">

                <thead
                    class="bg-admin-bg text-ink-soft
                           text-xs uppercase tracking-wide"
                >

                    <tr>

                        <th class="text-left px-5 py-3 font-semibold w-16">
                            STT
                        </th>

                        <th class="text-left px-5 py-3 font-semibold min-w-[310px]">
                            Sản phẩm
                        </th>

                        <th class="text-left px-5 py-3 font-semibold min-w-[175px]">
                            Danh mục
                        </th>

                        <th class="text-left px-5 py-3 font-semibold min-w-[175px]">
                            Giá
                        </th>

                        <th class="text-center px-5 py-3 font-semibold w-28">
                            Tồn kho
                        </th>

                        <th class="text-left px-5 py-3 font-semibold min-w-[145px]">
                            Trạng thái
                        </th>

                        <th class="text-right px-5 py-3 font-semibold min-w-[150px]">
                            Thao tác
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-admin-border">

                    @forelse ($products as $product)

                        <tr class="hover:bg-admin-bg/50 transition">

                            {{-- STT --}}
                            <td class="px-5 py-4 text-ink-soft">

                                {{ $products->firstItem() + $loop->index }}

                            </td>


                            {{-- PRODUCT --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div
                                        class="w-14 h-14 rounded-xl
                                               bg-admin-bg
                                               border border-admin-border
                                               flex items-center justify-center
                                               shrink-0 overflow-hidden"
                                    >

                                        @if ($product->image)

                                            <img
                                                src="{{ str_starts_with($product->image, 'http')
                                                    ? $product->image
                                                    : asset('storage/' . $product->image) }}"
                                                alt=""
                                                class="w-full h-full object-cover"
                                                onerror="
                                                    this.style.display='none';
                                                    this.nextElementSibling.style.display='flex';
                                                "
                                            >

                                            <span
                                                style="display:none;"
                                                class="w-full h-full
                                                       items-center justify-center
                                                       text-2xl"
                                            >
                                                📦
                                            </span>

                                        @else

                                            <span
                                                class="w-full h-full
                                                       flex items-center justify-center
                                                       text-2xl"
                                            >
                                                📦
                                            </span>

                                        @endif

                                    </div>


                                    <div class="min-w-0">

                                        <p class="font-semibold text-ink">
                                            {{ $product->name }}
                                        </p>

                                        <p
                                            class="text-xs text-ink-soft mt-0.5
                                                   max-w-[260px] truncate"
                                            title="{{ $product->slug }}"
                                        >
                                            {{ $product->slug }}
                                        </p>

                                        <p class="text-xs text-ink-soft mt-0.5">
                                            ID #{{ $product->id }}
                                        </p>

                                    </div>

                                </div>

                            </td>


                            {{-- CATEGORY --}}
                            <td class="px-5 py-4">

                                @if ($product->category)

                                    <span
                                        class="inline-flex px-2.5 py-1
                                               rounded-lg bg-admin-bg
                                               text-xs text-ink
                                               whitespace-nowrap"
                                    >
                                        {{ $product->category->name }}
                                    </span>

                                @else

                                    <span class="text-ink-soft text-xs">
                                        Chưa phân loại
                                    </span>

                                @endif

                            </td>


                            {{-- PRICE --}}
                            <td class="px-5 py-4">

                                <p class="font-semibold text-coral">
                                    {{ number_format($product->price, 0, ',', '.') }}đ
                                </p>


                                @if ($product->old_price)

                                    <p
                                        class="text-xs text-ink-soft
                                               line-through mt-1"
                                    >
                                        {{ number_format($product->old_price, 0, ',', '.') }}đ
                                    </p>

                                @endif


                                @if ($product->discount_percent)

                                    <span
                                        class="inline-flex mt-1
                                               px-2 py-0.5
                                               rounded-full bg-red-50
                                               text-red-500 text-xs font-semibold"
                                    >
                                        -{{ $product->discount_percent }}%
                                    </span>

                                @endif

                            </td>


                            {{-- STOCK --}}
                            <td class="px-5 py-4 text-center">

                                @if ($product->stock <= 10)

                                    <span
                                        class="inline-flex min-w-10 h-9
                                               px-2 items-center justify-center
                                               rounded-xl bg-red-50
                                               text-red-500 font-semibold"
                                        title="Tồn kho thấp"
                                    >
                                        {{ $product->stock }}
                                    </span>

                                @else

                                    <span
                                        class="inline-flex min-w-10 h-9
                                               px-2 items-center justify-center
                                               rounded-xl bg-admin-bg
                                               text-ink font-semibold"
                                    >
                                        {{ $product->stock }}
                                    </span>

                                @endif

                            </td>


                            {{-- STATUS --}}
                            <td class="px-5 py-4">

                                @if ($product->is_active)

                                    <span
                                        class="inline-flex items-center gap-2
                                               rounded-full bg-green-50
                                               px-3 py-1.5
                                               text-xs font-semibold
                                               text-green-600
                                               whitespace-nowrap"
                                    >

                                        <span
                                            class="w-2 h-2 rounded-full
                                                   bg-green-500"
                                        ></span>

                                        Đang bán

                                    </span>

                                @else

                                    <span
                                        class="inline-flex items-center gap-2
                                               rounded-full bg-gray-100
                                               px-3 py-1.5
                                               text-xs font-semibold
                                               text-gray-500
                                               whitespace-nowrap"
                                    >

                                        <span
                                            class="w-2 h-2 rounded-full
                                                   bg-gray-400"
                                        ></span>

                                        Đã ẩn

                                    </span>

                                @endif

                            </td>


                            {{-- ACTION --}}
                            <td class="px-5 py-4">

                                <div class="flex justify-end gap-2">

                                    <a
                                        href="{{ route('admin.products.edit', $product) }}"
                                        class="px-3.5 py-2 rounded-lg
                                               border border-admin-border
                                               text-sm text-ink
                                               hover:border-coral
                                               hover:text-coral transition"
                                    >
                                        Sửa
                                    </a>


                                    <button
                                        type="button"
                                        data-action="{{ route('admin.products.destroy', $product) }}"
                                        data-name="{{ $product->name }}"
                                        onclick="openDeleteProductModal(this)"
                                        class="px-3.5 py-2 rounded-lg
                                               bg-red-50 text-sm text-red-500
                                               hover:bg-red-100 transition"
                                    >
                                        Xóa
                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="px-5 py-20 text-center">

                                <div
                                    class="mx-auto w-16 h-16
                                           rounded-2xl bg-coral-light
                                           flex items-center justify-center
                                           text-3xl"
                                >
                                    🛍️
                                </div>

                                <p class="mt-4 font-semibold text-ink">
                                    Không tìm thấy sản phẩm
                                </p>

                                <p class="mt-1 text-sm text-ink-soft">

                                    @if (
                                        request()->filled('search')
                                        || request()->filled('category_id')
                                        || request()->filled('status')
                                        || request()->boolean('low_stock')
                                    )

                                        Hãy thử thay đổi bộ lọc tìm kiếm.

                                    @else

                                        Hãy tạo sản phẩm đầu tiên.

                                    @endif

                                </p>


                                @if (
                                    request()->filled('search')
                                    || request()->filled('category_id')
                                    || request()->filled('status')
                                    || request()->boolean('low_stock')
                                )

                                    <a
                                        href="{{ route('admin.products.index') }}"
                                        class="inline-flex mt-5 px-5 py-2.5
                                               rounded-xl border
                                               border-admin-border
                                               text-sm text-ink
                                               hover:bg-admin-bg"
                                    >
                                        Làm mới bộ lọc
                                    </a>

                                @else

                                    <a
                                        href="{{ route('admin.products.create') }}"
                                        class="btn-primary inline-block mt-5"
                                    >
                                        + Thêm sản phẩm
                                    </a>

                                @endif

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
                       sm:items-center sm:justify-between
                       gap-4 border-t border-admin-border
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
                        $currentPage = $products->currentPage();
                        $lastPage = $products->lastPage();

                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($lastPage, $currentPage + 2);

                        if ($currentPage <= 3) {
                            $endPage = min(5, $lastPage);
                        }

                        if ($currentPage >= $lastPage - 2) {
                            $startPage = max(1, $lastPage - 4);
                        }
                    @endphp


                    <div class="flex items-center gap-1">

                        {{-- PREVIOUS --}}
                        @if ($products->onFirstPage())

                            <span
                                class="w-10 h-10 rounded-xl
                                       border border-admin-border
                                       flex items-center justify-center
                                       text-gray-300 cursor-not-allowed"
                            >
                                ‹
                            </span>

                        @else

                            <a
                                href="{{ route(
                                    'admin.products.index',
                                    array_merge(
                                        request()->except('page'),
                                        ['page' => $currentPage - 1]
                                    )
                                ) }}"
                                class="w-10 h-10 rounded-xl
                                       border border-admin-border
                                       flex items-center justify-center
                                       text-ink
                                       hover:border-coral
                                       hover:text-coral
                                       hover:bg-coral-light/30
                                       transition"
                            >
                                ‹
                            </a>

                        @endif



                        {{-- FIRST PAGE --}}
                        @if ($startPage > 1)

                            <a
                                href="{{ route(
                                    'admin.products.index',
                                    array_merge(
                                        request()->except('page'),
                                        ['page' => 1]
                                    )
                                ) }}"
                                class="w-10 h-10 rounded-xl
                                       border border-admin-border
                                       flex items-center justify-center
                                       text-sm text-ink
                                       hover:border-coral
                                       hover:text-coral transition"
                            >
                                1
                            </a>


                            @if ($startPage > 2)

                                <span
                                    class="w-8 h-10
                                           flex items-center justify-center
                                           text-ink-soft"
                                >
                                    ...
                                </span>

                            @endif

                        @endif



                        {{-- PAGE WINDOW --}}
                        @for ($page = $startPage; $page <= $endPage; $page++)

                            @if ($page === $currentPage)

                                <span
                                    class="w-10 h-10 rounded-xl
                                           bg-coral text-white
                                           flex items-center justify-center
                                           text-sm font-semibold
                                           shadow-sm"
                                >
                                    {{ $page }}
                                </span>

                            @else

                                <a
                                    href="{{ route(
                                        'admin.products.index',
                                        array_merge(
                                            request()->except('page'),
                                            ['page' => $page]
                                        )
                                    ) }}"
                                    class="w-10 h-10 rounded-xl
                                           border border-admin-border
                                           flex items-center justify-center
                                           text-sm text-ink
                                           hover:border-coral
                                           hover:text-coral
                                           hover:bg-coral-light/30
                                           transition"
                                >
                                    {{ $page }}
                                </a>

                            @endif

                        @endfor



                        {{-- LAST PAGE --}}
                        @if ($endPage < $lastPage)

                            @if ($endPage < $lastPage - 1)

                                <span
                                    class="w-8 h-10
                                           flex items-center justify-center
                                           text-ink-soft"
                                >
                                    ...
                                </span>

                            @endif


                            <a
                                href="{{ route(
                                    'admin.products.index',
                                    array_merge(
                                        request()->except('page'),
                                        ['page' => $lastPage]
                                    )
                                ) }}"
                                class="w-10 h-10 rounded-xl
                                       border border-admin-border
                                       flex items-center justify-center
                                       text-sm text-ink
                                       hover:border-coral
                                       hover:text-coral transition"
                            >
                                {{ $lastPage }}
                            </a>

                        @endif



                        {{-- NEXT --}}
                        @if ($products->hasMorePages())

                            <a
                                href="{{ route(
                                    'admin.products.index',
                                    array_merge(
                                        request()->except('page'),
                                        ['page' => $currentPage + 1]
                                    )
                                ) }}"
                                class="w-10 h-10 rounded-xl
                                       border border-admin-border
                                       flex items-center justify-center
                                       text-ink
                                       hover:border-coral
                                       hover:text-coral
                                       hover:bg-coral-light/30
                                       transition"
                            >
                                ›
                            </a>

                        @else

                            <span
                                class="w-10 h-10 rounded-xl
                                       border border-admin-border
                                       flex items-center justify-center
                                       text-gray-300 cursor-not-allowed"
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
        DELETE MODAL
    ====================================================== --}}
    <div
        id="deleteProductModal"
        class="fixed inset-0 z-50 hidden
               items-center justify-center
               bg-black/40 px-4"
    >

        <div
            class="w-full max-w-md
                   rounded-2xl bg-white shadow-2xl"
            onclick="event.stopPropagation()"
        >

            <div class="p-6">

                <div class="flex items-start gap-4">

                    <div
                        class="w-12 h-12 shrink-0
                               rounded-full bg-red-50
                               text-red-500
                               flex items-center justify-center
                               text-xl"
                    >
                        !
                    </div>


                    <div class="flex-1">

                        <h3 class="text-lg font-semibold text-ink">
                            Xóa sản phẩm?
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-ink-soft">

                            Bạn có chắc muốn xóa

                            <strong
                                id="deleteProductName"
                                class="text-ink"
                            ></strong>?

                        </p>

                        <p class="text-xs text-red-500 mt-2">
                            Thao tác này không thể hoàn tác.
                        </p>

                    </div>

                </div>


                <div class="mt-6 flex justify-end gap-3">

                    <button
                        type="button"
                        onclick="closeDeleteProductModal()"
                        class="px-4 py-2.5 rounded-xl
                               border border-admin-border
                               text-sm text-ink
                               hover:bg-admin-bg transition"
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
                            class="px-4 py-2.5 rounded-xl
                                   bg-red-500 text-white
                                   text-sm font-semibold
                                   hover:bg-red-600 transition"
                        >
                            Xóa sản phẩm
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

@endsection



@push('scripts')

<script>

    function openDeleteProductModal(button) {

        const modal =
            document.getElementById('deleteProductModal');

        document.getElementById('deleteProductForm').action =
            button.dataset.action;

        document.getElementById('deleteProductName').textContent =
            button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }


    function closeDeleteProductModal() {

        const modal =
            document.getElementById('deleteProductModal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }


    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const modal =
                document.getElementById('deleteProductModal');

            if (!modal) {
                return;
            }

            modal.addEventListener(
                'click',
                function (event) {

                    if (event.target === modal) {
                        closeDeleteProductModal();
                    }

                }
            );

        }
    );


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