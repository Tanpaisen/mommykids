@extends('admin.layouts.app')

@section('page_title', 'Thùng rác danh mục')
@section('page_subtitle', 'Khôi phục hoặc xóa vĩnh viễn các danh mục đã xóa')

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
            Thùng rác danh mục
        </h2>

        <p class="text-sm text-ink-soft mt-1">
            Danh sách các danh mục đã được xóa mềm khỏi hệ thống.
        </p>
    </div>

    <a
        href="{{ route('admin.categories.index') }}"
        class="inline-flex items-center justify-center gap-2
               h-11 px-4
               rounded-xl
               border border-admin-border
               bg-white
               text-sm font-medium text-ink
               hover:bg-admin-bg
               transition"
    >
        ← Quay lại danh mục
    </a>
</div>


{{-- =====================================================
    SEARCH
====================================================== --}}
<div class="card mb-5">

    <form
        method="GET"
        action="{{ route('admin.categories.trash') }}"
        class="flex flex-col md:flex-row gap-3"
    >
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Tìm danh mục đã xóa theo tên hoặc slug..."
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
                href="{{ route('admin.categories.trash') }}"
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

        <table class="w-full min-w-[1050px] text-sm">

            <thead class="bg-admin-bg text-xs uppercase text-ink-soft">
                <tr>
                    <th class="text-left px-5 py-4">
                        STT
                    </th>

                    <th class="text-left px-5 py-4 min-w-[300px]">
                        Danh mục
                    </th>

                    <th class="text-left px-5 py-4">
                        Slug
                    </th>

                    <th class="text-center px-5 py-4">
                        Thứ tự
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

                @forelse ($categories as $category)

                    <tr class="hover:bg-admin-bg/40 transition">

                        {{-- STT --}}
                        <td class="px-5 py-4 text-ink-soft">
                            {{ $categories->firstItem() + $loop->index }}
                        </td>


                        {{-- CATEGORY --}}
                        <td class="px-5 py-4">

                            <div class="flex items-center gap-4">

                                {{-- IMAGE / ICON --}}
                                <div
                                    class="w-14 h-14
                                           shrink-0
                                           rounded-xl
                                           border border-admin-border
                                           bg-white
                                           overflow-hidden
                                           flex items-center justify-center"
                                >
                                    @if ($category->image)

                                        <img
                                            src="{{ str_starts_with($category->image, 'http')
                                                ? $category->image
                                                : asset('storage/' . $category->image) }}"
                                            alt="{{ $category->name }}"
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
                                                   text-2xl"
                                        >
                                            {{ $category->icon ?: '📦' }}
                                        </span>

                                    @else

                                        <span class="text-2xl">
                                            {{ $category->icon ?: '📦' }}
                                        </span>

                                    @endif
                                </div>


                                <div class="min-w-0">
                                    <p class="font-semibold text-ink">
                                        {{ $category->name }}
                                    </p>

                                    <p class="text-xs text-ink-soft mt-1">
                                        ID #{{ $category->id }}
                                    </p>
                                </div>

                            </div>

                        </td>


                        {{-- SLUG --}}
                        <td class="px-5 py-4">

                            <span
                                class="inline-flex
                                       rounded-lg
                                       bg-admin-bg
                                       px-2.5 py-1
                                       text-xs text-ink"
                            >
                                {{ $category->slug }}
                            </span>

                        </td>


                        {{-- SORT --}}
                        <td class="px-5 py-4 text-center">

                            <span
                                class="inline-flex
                                       min-w-9 h-9
                                       items-center justify-center
                                       rounded-xl
                                       bg-admin-bg
                                       px-2
                                       font-semibold"
                            >
                                {{ $category->sort_order }}
                            </span>

                        </td>


                        {{-- DELETED BY --}}
                        <td class="px-5 py-4">

                            @if ($category->deleted_by)

                                <div>
                                    <p class="font-medium text-ink">
                                        Admin #{{ $category->deleted_by }}
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
                                           text-xs text-gray-500"
                                >
                                    Chưa xác định
                                </span>

                            @endif

                        </td>


                        {{-- DELETED AT --}}
                        <td class="px-5 py-4">

                            @if ($category->deleted_at)

                                <p class="font-medium text-ink">
                                    {{ $category->deleted_at->format('H:i') }}
                                </p>

                                <p class="text-xs text-ink-soft mt-1">
                                    {{ $category->deleted_at->format('d/m/Y') }}
                                </p>

                            @else

                                <span class="text-ink-soft">
                                    —
                                </span>

                            @endif

                        </td>


                        {{-- ACTION --}}
                        <td class="px-5 py-4">

                            <div class="flex justify-end gap-2">

                                {{-- RESTORE --}}
                                <button
                                    type="button"
                                    data-action="{{ route(
                                        'admin.categories.restore',
                                        $category->id
                                    ) }}"
                                    data-name="{{ $category->name }}"
                                    onclick="openRestoreCategoryModal(this)"
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
                                        'admin.categories.forceDelete',
                                        $category->id
                                    ) }}"
                                    data-name="{{ $category->name }}"
                                    onclick="openForceDeleteCategoryModal(this)"
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
                        <td colspan="7" class="py-20 text-center">

                            <div class="text-5xl">
                                🗑️
                            </div>

                            <p class="mt-4 font-semibold text-ink">
                                Thùng rác danh mục đang trống
                            </p>

                            <p class="mt-1 text-sm text-ink-soft">
                                Chưa có danh mục nào bị xóa.
                            </p>

                            <a
                                href="{{ route('admin.categories.index') }}"
                                class="inline-flex
                                       mt-5
                                       px-5 py-2.5
                                       rounded-xl
                                       bg-coral
                                       text-white
                                       text-sm
                                       font-semibold"
                            >
                                Quay lại danh mục
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
    @if ($categories->total() > 0)

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
                    {{ $categories->firstItem() }}
                </strong>

                -

                <strong class="text-ink">
                    {{ $categories->lastItem() }}
                </strong>

                trong

                <strong class="text-ink">
                    {{ $categories->total() }}
                </strong>

                danh mục đã xóa
            </p>

            @if ($categories->hasPages())
                <div>
                    {{ $categories->links() }}
                </div>
            @endif

        </div>

    @endif

</div>


{{-- =====================================================
    RESTORE CATEGORY MODAL
====================================================== --}}
<div
    id="restoreCategoryModal"
    class="fixed inset-0 z-50
           hidden items-center justify-center
           bg-black/40 px-4"
>

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6">

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
                    Khôi phục danh mục?
                </h3>

                <p class="text-sm text-ink-soft mt-2 leading-6">
                    Bạn có chắc muốn khôi phục

                    <strong
                        id="restoreCategoryName"
                        class="text-ink"
                    ></strong>?
                </p>

                <p class="text-xs text-ink-soft mt-2">
                    Danh mục sẽ được đưa trở lại danh sách quản lý.
                </p>

            </div>

        </div>


        <div class="flex justify-end gap-3 mt-6">

            <button
                type="button"
                onclick="closeRestoreCategoryModal()"
                class="border border-admin-border
                       rounded-xl
                       px-4 py-2.5
                       text-sm
                       hover:bg-admin-bg"
            >
                Hủy
            </button>

            <form
                id="restoreCategoryForm"
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
    FORCE DELETE CATEGORY MODAL
====================================================== --}}
<div
    id="forceDeleteCategoryModal"
    class="fixed inset-0 z-50
           hidden items-center justify-center
           bg-black/40 px-4"
>

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6">

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
                    Xóa vĩnh viễn danh mục?
                </h3>

                <p class="text-sm text-ink-soft mt-2 leading-6">
                    Bạn có chắc muốn xóa vĩnh viễn

                    <strong
                        id="forceDeleteCategoryName"
                        class="text-ink"
                    ></strong>?
                </p>

                <p class="text-xs text-red-500 mt-2">
                    Hành động này không thể khôi phục.
                    Ảnh danh mục cũng sẽ bị xóa khỏi hệ thống.
                </p>

            </div>

        </div>


        <div class="flex justify-end gap-3 mt-6">

            <button
                type="button"
                onclick="closeForceDeleteCategoryModal()"
                class="border border-admin-border
                       rounded-xl
                       px-4 py-2.5
                       text-sm
                       hover:bg-admin-bg"
            >
                Hủy
            </button>

            <form
                id="forceDeleteCategoryForm"
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
    function openRestoreCategoryModal(button) {
        const modal = document.getElementById(
            'restoreCategoryModal'
        );

        document.getElementById(
            'restoreCategoryForm'
        ).action = button.dataset.action;

        document.getElementById(
            'restoreCategoryName'
        ).textContent = button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }

    function closeRestoreCategoryModal() {
        const modal = document.getElementById(
            'restoreCategoryModal'
        );

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }

    function openForceDeleteCategoryModal(button) {
        const modal = document.getElementById(
            'forceDeleteCategoryModal'
        );

        document.getElementById(
            'forceDeleteCategoryForm'
        ).action = button.dataset.action;

        document.getElementById(
            'forceDeleteCategoryName'
        ).textContent = button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }

    function closeForceDeleteCategoryModal() {
        const modal = document.getElementById(
            'forceDeleteCategoryModal'
        );

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeRestoreCategoryModal();
                closeForceDeleteCategoryModal();
            }
        }
    );
</script>

@endpush