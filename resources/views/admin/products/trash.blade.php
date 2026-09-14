@extends('admin.layouts.app')

@section('page_title', 'Thùng rác sản phẩm')
@section('page_subtitle', 'Khôi phục hoặc xóa vĩnh viễn các sản phẩm đã xóa')


@section('content')

{{-- =====================================================
    HEADER
====================================================== --}}
<div
    class="flex flex-col sm:flex-row
           sm:items-center sm:justify-between
           gap-4 mb-5"
>

    <div>

        <h2 class="text-lg font-semibold text-ink">
            Thùng rác sản phẩm
        </h2>

        <p class="text-sm text-ink-soft mt-1">
            Danh sách các sản phẩm đã được xóa mềm.
        </p>

    </div>


    <a
        href="{{ route('admin.products.index') }}"
        class="inline-flex
               items-center justify-center gap-2
               h-11 px-4
               rounded-xl
               border border-admin-border
               bg-white
               text-sm font-medium text-ink
               hover:bg-admin-bg
               transition"
    >
        ← Quay lại sản phẩm
    </a>

</div>



{{-- =====================================================
    SEARCH
====================================================== --}}
<div class="card mb-5">

    <form
        method="GET"
        action="{{ route('admin.products.trash') }}"
        class="flex flex-col md:flex-row gap-3"
    >

        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Tìm sản phẩm đã xóa..."
            class="flex-1
                   border border-admin-border
                   rounded-xl
                   px-4 py-3
                   bg-white
                   outline-none
                   focus:border-coral
                   focus:ring-2
                   focus:ring-coral/10"
        >


        <button
            type="submit"
            class="px-6 py-3
                   rounded-xl
                   bg-coral
                   text-white
                   font-semibold
                   hover:opacity-90
                   transition"
        >
            Tìm kiếm
        </button>


        @if (request()->filled('search'))

            <a
                href="{{ route('admin.products.trash') }}"
                class="px-5 py-3
                       rounded-xl
                       border border-admin-border
                       bg-white
                       text-center
                       text-ink
                       hover:bg-admin-bg
                       transition"
            >
                Xóa lọc
            </a>

        @endif

    </form>

</div>



{{-- =====================================================
    TABLE
====================================================== --}}
<div class="card overflow-hidden">

    <div class="overflow-x-auto">

        <table class="w-full min-w-[1100px] text-sm">

            <thead class="bg-admin-bg text-xs uppercase text-ink-soft">

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
                        Người xóa
                    </th>

                    <th class="text-left px-5 py-4">
                        Thời gian xóa
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
                                           bg-white
                                           overflow-hidden
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
                                        class="text-xs
                                               text-ink-soft
                                               mt-1
                                               max-w-[300px]
                                               truncate"
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
                                       rounded-lg
                                       bg-admin-bg
                                       px-2.5 py-1
                                       text-xs"
                            >
                                {{ $product->category?->name ?? 'Không có danh mục' }}
                            </span>

                        </td>


                        {{-- DELETED BY --}}
                        <td class="px-5 py-4">

                            @if ($product->deleted_by)

                                <div>

                                    <p class="font-medium text-ink">
                                        Admin #{{ $product->deleted_by }}
                                    </p>

                                    <p class="text-xs text-ink-soft mt-1">
                                        ID người thực hiện
                                    </p>

                                </div>

                            @else

                                <span
                                    class="inline-flex
                                           rounded-full
                                           bg-gray-100
                                           px-3 py-1
                                           text-xs
                                           text-gray-500"
                                >
                                    Chưa xác định
                                </span>

                            @endif

                        </td>


                        {{-- DELETED AT --}}
                        <td class="px-5 py-4">

                            @if ($product->deleted_at)

                                <p class="font-medium text-ink">
                                    {{ $product->deleted_at->format('H:i') }}
                                </p>

                                <p class="text-xs text-ink-soft mt-1">
                                    {{ $product->deleted_at->format('d/m/Y') }}
                                </p>

                            @else

                                <span class="text-ink-soft">
                                    —
                                </span>

                            @endif

                        </td>


                        {{-- ACTION --}}
                        <td class="px-5 py-4">

                            <div
                                class="flex
                                       items-center
                                       justify-end
                                       gap-2"
                            >

                                {{-- RESTORE --}}
                                <button
                                    type="button"
                                    data-action="{{ route(
                                        'admin.products.restore',
                                        $product->id
                                    ) }}"
                                    data-name="{{ $product->name }}"
                                    onclick="openRestoreProductModal(this)"
                                    class="inline-flex
                                           items-center gap-2
                                           rounded-xl
                                           bg-green-50
                                           px-4 py-2.5
                                           text-sm
                                           font-medium
                                           text-green-600
                                           hover:bg-green-100
                                           transition"
                                >
                                    ↶ Khôi phục
                                </button>


                                {{-- FORCE DELETE --}}
                                <button
                                    type="button"
                                    data-action="{{ route(
                                        'admin.products.forceDelete',
                                        $product->id
                                    ) }}"
                                    data-name="{{ $product->name }}"
                                    onclick="openForceDeleteProductModal(this)"
                                    class="inline-flex
                                           items-center gap-2
                                           rounded-xl
                                           bg-red-50
                                           px-4 py-2.5
                                           text-sm
                                           font-medium
                                           text-red-500
                                           hover:bg-red-100
                                           transition"
                                >
                                    🗑 Xóa vĩnh viễn
                                </button>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="py-20 text-center"
                        >

                            <div class="text-5xl">
                                🗑️
                            </div>

                            <p class="mt-4 font-semibold text-ink">
                                Thùng rác đang trống
                            </p>

                            <p class="mt-1 text-sm text-ink-soft">
                                Chưa có sản phẩm nào bị xóa.
                            </p>


                            <a
                                href="{{ route('admin.products.index') }}"
                                class="inline-flex
                                       mt-5
                                       px-5 py-2.5
                                       rounded-xl
                                       bg-coral
                                       text-white
                                       text-sm
                                       font-semibold"
                            >
                                Quay lại sản phẩm
                            </a>

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

                sản phẩm đã xóa

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
                                   hover:text-coral"
                        >
                            ‹
                        </a>

                    @endif


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

                            <span class="px-2 text-ink-soft">
                                ...
                            </span>

                        @endif

                    @endif


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
                                       hover:text-coral"
                            >
                                {{ $page }}
                            </a>

                        @endif

                    @endfor


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
                                   flex items-center justify-center"
                        >
                            {{ $last }}
                        </a>

                    @endif


                    @if ($products->hasMorePages())

                        <a
                            href="{{ $products->nextPageUrl() }}"
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   hover:border-coral
                                   hover:text-coral"
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
    RESTORE MODAL
====================================================== --}}
<div
    id="restoreProductModal"
    class="fixed inset-0 z-50
           hidden items-center justify-center
           bg-black/40 px-4"
>

    <div
        class="w-full max-w-md
               bg-white
               rounded-2xl
               shadow-2xl
               p-6"
    >

        <div class="flex items-start gap-4">

            <div
                class="w-12 h-12
                       shrink-0
                       rounded-full
                       bg-green-50
                       text-green-600
                       flex items-center justify-center
                       text-xl"
            >
                ↶
            </div>


            <div class="flex-1">

                <h3 class="text-lg font-semibold text-ink">
                    Khôi phục sản phẩm?
                </h3>

                <p class="text-sm text-ink-soft mt-2 leading-6">

                    Bạn có chắc muốn khôi phục

                    <strong
                        id="restoreProductName"
                        class="text-ink"
                    ></strong>?

                </p>

                <p class="text-xs text-ink-soft mt-2">
                    Sản phẩm sẽ được đưa trở lại danh sách sản phẩm.
                </p>

            </div>

        </div>


        <div class="flex justify-end gap-3 mt-6">

            <button
                type="button"
                onclick="closeRestoreProductModal()"
                class="border border-admin-border
                       rounded-xl
                       px-4 py-2.5
                       text-sm
                       hover:bg-admin-bg"
            >
                Hủy
            </button>


            <form
                id="restoreProductForm"
                method="POST"
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="bg-green-500
                           text-white
                           rounded-xl
                           px-4 py-2.5
                           text-sm font-semibold
                           hover:bg-green-600"
                >
                    Khôi phục
                </button>

            </form>

        </div>

    </div>

</div>



{{-- =====================================================
    FORCE DELETE MODAL
====================================================== --}}
<div
    id="forceDeleteProductModal"
    class="fixed inset-0 z-50
           hidden items-center justify-center
           bg-black/40 px-4"
>

    <div
        class="w-full max-w-md
               bg-white
               rounded-2xl
               shadow-2xl
               p-6"
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
                ⚠️
            </div>


            <div class="flex-1">

                <h3 class="text-lg font-semibold text-ink">
                    Xóa vĩnh viễn?
                </h3>

                <p class="text-sm text-ink-soft mt-2 leading-6">

                    Bạn có chắc muốn xóa vĩnh viễn

                    <strong
                        id="forceDeleteProductName"
                        class="text-ink"
                    ></strong>?

                </p>

                <p class="text-xs text-red-500 mt-2">
                    Hành động này không thể khôi phục.
                    Ảnh và các liên kết của sản phẩm cũng sẽ bị xóa.
                </p>

            </div>

        </div>


        <div class="flex justify-end gap-3 mt-6">

            <button
                type="button"
                onclick="closeForceDeleteProductModal()"
                class="border border-admin-border
                       rounded-xl
                       px-4 py-2.5
                       text-sm
                       hover:bg-admin-bg"
            >
                Hủy
            </button>


            <form
                id="forceDeleteProductForm"
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
                           hover:bg-red-600"
                >
                    Xóa vĩnh viễn
                </button>

            </form>

        </div>

    </div>

</div>

@endsection



@push('scripts')

<script>
    function openRestoreProductModal(button) {
        const modal = document.getElementById(
            'restoreProductModal'
        );

        document.getElementById(
            'restoreProductForm'
        ).action = button.dataset.action;

        document.getElementById(
            'restoreProductName'
        ).textContent = button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }


    function closeRestoreProductModal() {
        const modal = document.getElementById(
            'restoreProductModal'
        );

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }


    function openForceDeleteProductModal(button) {
        const modal = document.getElementById(
            'forceDeleteProductModal'
        );

        document.getElementById(
            'forceDeleteProductForm'
        ).action = button.dataset.action;

        document.getElementById(
            'forceDeleteProductName'
        ).textContent = button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }


    function closeForceDeleteProductModal() {
        const modal = document.getElementById(
            'forceDeleteProductModal'
        );

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }


    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key === 'Escape') {
                closeRestoreProductModal();
                closeForceDeleteProductModal();
            }

        }
    );
</script>

@endpush