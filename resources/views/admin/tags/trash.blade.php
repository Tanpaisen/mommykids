@extends('admin.layouts.app')

@section('page_title', 'Thùng rác thuộc tính')
@section('page_subtitle', 'Khôi phục hoặc xóa vĩnh viễn các thuộc tính đã xóa')

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
            Thùng rác thuộc tính
        </h2>

        <p class="text-sm text-ink-soft mt-1">
            Danh sách các thuộc tính, thương hiệu và tag đã được xóa mềm.
        </p>
    </div>

    <a
        href="{{ route('admin.categories.index', ['tab' => 'tags']) }}"
        class="inline-flex items-center justify-center gap-2
               h-11 px-4
               rounded-xl
               border border-admin-border
               bg-white
               text-sm font-medium text-ink
               hover:bg-admin-bg
               transition"
    >
        ← Quay lại thuộc tính
    </a>

</div>


{{-- =====================================================
    FILTER
====================================================== --}}
<div class="card mb-5">

    <form
        method="GET"
        action="{{ route('admin.tags.trash') }}"
        class="grid grid-cols-1
               md:grid-cols-[1fr_260px_auto]
               gap-3"
    >

        {{-- SEARCH --}}
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Tìm thuộc tính đã xóa theo tên hoặc slug..."
            class="border border-admin-border
                   rounded-xl
                   px-4 py-3
                   bg-white
                   outline-none
                   focus:border-coral
                   focus:ring-2
                   focus:ring-coral/10"
        >


        {{-- TYPE --}}
        <select
            name="type"
            class="border border-admin-border
                   rounded-xl
                   px-4 py-3
                   bg-white
                   outline-none
                   focus:border-coral"
        >
            <option value="">
                Tất cả loại
            </option>

            <option
                value="attribute"
                @selected(request('type') === 'attribute')
            >
                Thuộc tính
            </option>

            <option
                value="brand"
                @selected(request('type') === 'brand')
            >
                Thương hiệu
            </option>

            <option
                value="stage"
                @selected(request('type') === 'stage')
            >
                Giai đoạn
            </option>
        </select>


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
            Lọc
        </button>

    </form>


    @if (
        request()->filled('search')
        || request()->filled('type')
    )

        <div class="mt-3">

            <a
                href="{{ route('admin.tags.trash') }}"
                class="text-sm text-ink-soft
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

        <table class="w-full min-w-[1000px] text-sm">

            <thead class="bg-admin-bg text-xs uppercase text-ink-soft">
                <tr>

                    <th class="text-left px-5 py-4">
                        STT
                    </th>

                    <th class="text-left px-5 py-4 min-w-[260px]">
                        Tên thuộc tính
                    </th>

                    <th class="text-left px-5 py-4">
                        Slug
                    </th>

                    <th class="text-left px-5 py-4">
                        Loại
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

                @forelse ($tags as $tag)

                    <tr class="hover:bg-admin-bg/40 transition">

                        {{-- STT --}}
                        <td class="px-5 py-4 text-ink-soft">
                            {{ $tags->firstItem() + $loop->index }}
                        </td>


                        {{-- NAME --}}
                        <td class="px-5 py-4">

                            <div>
                                <p class="font-semibold text-ink">
                                    {{ $tag->name }}
                                </p>

                                <p class="text-xs text-ink-soft mt-1">
                                    ID #{{ $tag->id }}
                                </p>
                            </div>

                        </td>


                        {{-- SLUG --}}
                        <td class="px-5 py-4">

                            <span
                                class="inline-flex
                                       rounded-lg
                                       bg-admin-bg
                                       px-2.5 py-1
                                       text-xs"
                            >
                                {{ $tag->slug }}
                            </span>

                        </td>


                        {{-- TYPE --}}
                        <td class="px-5 py-4">

                            @if ($tag->type === 'attribute')

                                <span
                                    class="inline-flex
                                           rounded-full
                                           bg-blue-50
                                           text-blue-600
                                           px-3 py-1.5
                                           text-xs font-semibold"
                                >
                                    Thuộc tính
                                </span>

                            @elseif ($tag->type === 'brand')

                                <span
                                    class="inline-flex
                                           rounded-full
                                           bg-amber-50
                                           text-amber-600
                                           px-3 py-1.5
                                           text-xs font-semibold"
                                >
                                    Thương hiệu
                                </span>

                            @elseif ($tag->type === 'stage')

                                <span
                                    class="inline-flex
                                           rounded-full
                                           bg-purple-50
                                           text-purple-600
                                           px-3 py-1.5
                                           text-xs font-semibold"
                                >
                                    Giai đoạn
                                </span>

                            @else

                                <span
                                    class="inline-flex
                                           rounded-full
                                           bg-gray-100
                                           text-gray-500
                                           px-3 py-1.5
                                           text-xs"
                                >
                                    {{ $tag->type }}
                                </span>

                            @endif

                        </td>


                        {{-- DELETED BY --}}
                        <td class="px-5 py-4">

                            @if ($tag->deleted_by)

                                <div>
                                    <p class="font-medium text-ink">
                                        Admin #{{ $tag->deleted_by }}
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

                            @if ($tag->deleted_at)

                                <p class="font-medium text-ink">
                                    {{ $tag->deleted_at->format('H:i') }}
                                </p>

                                <p class="text-xs text-ink-soft mt-1">
                                    {{ $tag->deleted_at->format('d/m/Y') }}
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
                                        'admin.tags.restore',
                                        $tag->id
                                    ) }}"
                                    data-name="{{ $tag->name }}"
                                    onclick="openRestoreTagModal(this)"
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
                                        'admin.tags.forceDelete',
                                        $tag->id
                                    ) }}"
                                    data-name="{{ $tag->name }}"
                                    onclick="openForceDeleteTagModal(this)"
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
                                Thùng rác thuộc tính đang trống
                            </p>

                            <p class="mt-1 text-sm text-ink-soft">
                                Chưa có thuộc tính nào bị xóa.
                            </p>

                            <a
                                href="{{ route(
                                    'admin.categories.index',
                                    ['tab' => 'tags']
                                ) }}"
                                class="inline-flex
                                       mt-5
                                       px-5 py-2.5
                                       rounded-xl
                                       bg-coral
                                       text-white
                                       text-sm
                                       font-semibold"
                            >
                                Quay lại thuộc tính
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
    @if ($tags->total() > 0)

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
                    {{ $tags->firstItem() }}
                </strong>

                -

                <strong class="text-ink">
                    {{ $tags->lastItem() }}
                </strong>

                trong

                <strong class="text-ink">
                    {{ $tags->total() }}
                </strong>

                thuộc tính đã xóa
            </p>

            @if ($tags->hasPages())
                <div>
                    {{ $tags->links() }}
                </div>
            @endif

        </div>

    @endif

</div>


{{-- =====================================================
    RESTORE TAG MODAL
====================================================== --}}
<div
    id="restoreTagModal"
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
                    Khôi phục thuộc tính?
                </h3>

                <p class="text-sm text-ink-soft mt-2 leading-6">
                    Bạn có chắc muốn khôi phục

                    <strong
                        id="restoreTagName"
                        class="text-ink"
                    ></strong>?
                </p>

                <p class="text-xs text-ink-soft mt-2">
                    Thuộc tính sẽ được đưa trở lại danh sách quản lý.
                </p>

            </div>

        </div>


        <div class="flex justify-end gap-3 mt-6">

            <button
                type="button"
                onclick="closeRestoreTagModal()"
                class="border border-admin-border
                       rounded-xl
                       px-4 py-2.5
                       text-sm
                       hover:bg-admin-bg"
            >
                Hủy
            </button>

            <form
                id="restoreTagForm"
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
    FORCE DELETE TAG MODAL
====================================================== --}}
<div
    id="forceDeleteTagModal"
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
                    Xóa vĩnh viễn thuộc tính?
                </h3>

                <p class="text-sm text-ink-soft mt-2 leading-6">
                    Bạn có chắc muốn xóa vĩnh viễn

                    <strong
                        id="forceDeleteTagName"
                        class="text-ink"
                    ></strong>?
                </p>

                <p class="text-xs text-red-500 mt-2">
                    Hành động này không thể khôi phục.
                    Liên kết của thuộc tính với sản phẩm cũng sẽ bị xóa.
                </p>

            </div>

        </div>


        <div class="flex justify-end gap-3 mt-6">

            <button
                type="button"
                onclick="closeForceDeleteTagModal()"
                class="border border-admin-border
                       rounded-xl
                       px-4 py-2.5
                       text-sm
                       hover:bg-admin-bg"
            >
                Hủy
            </button>

            <form
                id="forceDeleteTagForm"
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
    function openRestoreTagModal(button) {
        const modal = document.getElementById(
            'restoreTagModal'
        );

        document.getElementById(
            'restoreTagForm'
        ).action = button.dataset.action;

        document.getElementById(
            'restoreTagName'
        ).textContent = button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }

    function closeRestoreTagModal() {
        const modal = document.getElementById(
            'restoreTagModal'
        );

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }

    function openForceDeleteTagModal(button) {
        const modal = document.getElementById(
            'forceDeleteTagModal'
        );

        document.getElementById(
            'forceDeleteTagForm'
        ).action = button.dataset.action;

        document.getElementById(
            'forceDeleteTagName'
        ).textContent = button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }

    function closeForceDeleteTagModal() {
        const modal = document.getElementById(
            'forceDeleteTagModal'
        );

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeRestoreTagModal();
                closeForceDeleteTagModal();
            }
        }
    );
</script>

@endpush