@extends('admin.layouts.app')

@section('page_title', 'Thùng rác giai đoạn')
@section('page_subtitle', 'Khôi phục hoặc xóa vĩnh viễn các giai đoạn đã xóa')

@section('content')

<div
    class="flex flex-col sm:flex-row
           sm:items-center sm:justify-between
           gap-4 mb-5"
>

    <div>

        <h2 class="text-lg font-semibold text-ink">
            Thùng rác giai đoạn
        </h2>

        <p class="text-sm text-ink-soft mt-1">
            Danh sách các giai đoạn đã được xóa mềm.
        </p>

    </div>

    <a
        href="{{ route('admin.stages.index') }}"
        class="inline-flex items-center justify-center
               gap-2 h-11 px-4 rounded-xl
               border border-admin-border
               bg-white text-sm font-medium text-ink
               hover:bg-admin-bg transition"
    >
        ← Quay lại giai đoạn
    </a>

</div>


<div class="card mb-5">

    <form
        method="GET"
        action="{{ route('admin.stages.trash') }}"
        class="flex flex-col md:flex-row gap-3"
    >

        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Tìm giai đoạn đã xóa..."
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
                href="{{ route('admin.stages.trash') }}"
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


<div class="card overflow-hidden">

    <div class="overflow-x-auto">

        <table class="w-full min-w-[1050px] text-sm">

            <thead
                class="bg-admin-bg
                       text-xs uppercase
                       text-ink-soft"
            >

                <tr>

                    <th class="text-left px-5 py-4">
                        STT
                    </th>

                    <th class="text-left px-5 py-4 min-w-[300px]">
                        Giai đoạn
                    </th>

                    <th class="text-left px-5 py-4">
                        Độ tuổi
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

                @forelse ($stages as $stage)

                    <tr class="hover:bg-admin-bg/40 transition">

                        <td class="px-5 py-4 text-ink-soft">

                            {{ $stages->firstItem() + $loop->index }}

                        </td>


                        <td class="px-5 py-4">

                            <div class="flex items-center gap-3">

                                <div
                                    class="w-11 h-11
                                           rounded-xl bg-coral-light
                                           text-coral
                                           flex items-center justify-center
                                           text-lg shrink-0"
                                >

                                    {{ $stage->icon
                                        ?: strtoupper(
                                            mb_substr(
                                                $stage->name,
                                                0,
                                                1
                                            )
                                        )
                                    }}

                                </div>

                                <div>

                                    <p class="font-semibold text-ink">
                                        {{ $stage->name }}
                                    </p>

                                    <p class="text-xs text-ink-soft mt-1">
                                        ID #{{ $stage->id }}
                                    </p>

                                </div>

                            </div>

                        </td>


                        <td class="px-5 py-4 text-ink">

                            @if (
                                !is_null($stage->age_from)
                                && !is_null($stage->age_to)
                            )

                                {{ $stage->age_from }}
                                -
                                {{ $stage->age_to }}
                                tháng

                            @elseif (!is_null($stage->age_from))

                                Từ {{ $stage->age_from }} tháng

                            @elseif (!is_null($stage->age_to))

                                Đến {{ $stage->age_to }} tháng

                            @else

                                <span class="text-ink-soft">
                                    Chưa xác định
                                </span>

                            @endif

                        </td>


                        <td class="px-5 py-4">

                            @if ($stage->deleted_by)

                                <div>

                                    <p class="font-medium text-ink">
                                        Admin #{{ $stage->deleted_by }}
                                    </p>

                                    <p class="text-xs text-ink-soft mt-1">
                                        ID người thực hiện
                                    </p>

                                </div>

                            @else

                                <span
                                    class="inline-flex
                                           rounded-full bg-gray-100
                                           px-3 py-1
                                           text-xs text-gray-500"
                                >
                                    Chưa xác định
                                </span>

                            @endif

                        </td>


                        <td class="px-5 py-4">

                            @if ($stage->deleted_at)

                                <p class="font-medium text-ink">
                                    {{ $stage->deleted_at->format('H:i') }}
                                </p>

                                <p class="text-xs text-ink-soft mt-1">
                                    {{ $stage->deleted_at->format('d/m/Y') }}
                                </p>

                            @else

                                <span class="text-ink-soft">
                                    —
                                </span>

                            @endif

                        </td>


                        <td class="px-5 py-4">

                            <div class="flex justify-end gap-2">

                                <button
                                    type="button"
                                    data-action="{{ route(
                                        'admin.stages.restore',
                                        $stage->id
                                    ) }}"
                                    data-name="{{ $stage->name }}"
                                    onclick="openRestoreStageModal(this)"
                                    class="inline-flex items-center gap-2
                                           rounded-xl bg-green-50
                                           px-4 py-2.5
                                           text-sm font-medium
                                           text-green-600
                                           hover:bg-green-100
                                           transition"
                                >
                                    ↶ Khôi phục
                                </button>


                                <button
                                    type="button"
                                    data-action="{{ route(
                                        'admin.stages.forceDelete',
                                        $stage->id
                                    ) }}"
                                    data-name="{{ $stage->name }}"
                                    onclick="openForceDeleteStageModal(this)"
                                    class="inline-flex items-center gap-2
                                           rounded-xl bg-red-50
                                           px-4 py-2.5
                                           text-sm font-medium
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
                                Thùng rác giai đoạn đang trống
                            </p>

                            <p class="mt-1 text-sm text-ink-soft">
                                Chưa có giai đoạn nào bị xóa.
                            </p>

                            <a
                                href="{{ route('admin.stages.index') }}"
                                class="inline-flex mt-5
                                       px-5 py-2.5
                                       rounded-xl
                                       bg-coral
                                       text-white
                                       text-sm font-semibold"
                            >
                                Quay lại giai đoạn
                            </a>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    @if ($stages->hasPages())

        <div
            class="border-t border-admin-border
                   px-5 py-4"
        >
            {{ $stages->links() }}
        </div>

    @endif

</div>


{{-- Modal khôi phục --}}
<div
    id="restoreStageModal"
    class="fixed inset-0 z-50
           hidden items-center justify-center
           bg-black/40 px-4"
>

    <div
        class="w-full max-w-md
               bg-white rounded-2xl
               shadow-2xl p-6"
    >

        <h3 class="text-lg font-semibold text-ink">
            Khôi phục giai đoạn?
        </h3>

        <p class="text-sm text-ink-soft mt-2 leading-6">

            Bạn có chắc muốn khôi phục

            <strong
                id="restoreStageName"
                class="text-ink"
            ></strong>?

        </p>

        <div class="flex justify-end gap-3 mt-6">

            <button
                type="button"
                onclick="closeRestoreStageModal()"
                class="border border-admin-border
                       rounded-xl
                       px-4 py-2.5
                       text-sm
                       hover:bg-admin-bg"
            >
                Hủy
            </button>

            <form
                id="restoreStageForm"
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


{{-- Modal xóa vĩnh viễn --}}
<div
    id="forceDeleteStageModal"
    class="fixed inset-0 z-50
           hidden items-center justify-center
           bg-black/40 px-4"
>

    <div
        class="w-full max-w-md
               bg-white rounded-2xl
               shadow-2xl p-6"
    >

        <h3 class="text-lg font-semibold text-ink">
            Xóa vĩnh viễn giai đoạn?
        </h3>

        <p class="text-sm text-ink-soft mt-2 leading-6">

            Bạn có chắc muốn xóa vĩnh viễn

            <strong
                id="forceDeleteStageName"
                class="text-ink"
            ></strong>?

        </p>

        <p class="text-xs text-red-500 mt-2">
            Hành động này không thể khôi phục.
            Liên kết với sản phẩm cũng sẽ bị xóa.
        </p>

        <div class="flex justify-end gap-3 mt-6">

            <button
                type="button"
                onclick="closeForceDeleteStageModal()"
                class="border border-admin-border
                       rounded-xl
                       px-4 py-2.5
                       text-sm
                       hover:bg-admin-bg"
            >
                Hủy
            </button>

            <form
                id="forceDeleteStageForm"
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
    function openRestoreStageModal(button) {
        const modal =
            document.getElementById(
                'restoreStageModal'
            );

        document.getElementById(
            'restoreStageForm'
        ).action =
            button.dataset.action;

        document.getElementById(
            'restoreStageName'
        ).textContent =
            button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow =
            'hidden';
    }

    function closeRestoreStageModal() {
        const modal =
            document.getElementById(
                'restoreStageModal'
            );

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow =
            '';
    }

    function openForceDeleteStageModal(button) {
        const modal =
            document.getElementById(
                'forceDeleteStageModal'
            );

        document.getElementById(
            'forceDeleteStageForm'
        ).action =
            button.dataset.action;

        document.getElementById(
            'forceDeleteStageName'
        ).textContent =
            button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow =
            'hidden';
    }

    function closeForceDeleteStageModal() {
        const modal =
            document.getElementById(
                'forceDeleteStageModal'
            );

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow =
            '';
    }

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeRestoreStageModal();
                closeForceDeleteStageModal();
            }
        }
    );
</script>

@endpush