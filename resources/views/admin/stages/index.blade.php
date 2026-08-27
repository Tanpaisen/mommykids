@extends('admin.layouts.app')

@section('page_title', 'Giai đoạn của bé')
@section('page_subtitle', 'Quản lý các mốc phát triển để gắn với sản phẩm và bài viết')

@section('page_actions')
    <a href="{{ route('admin.stages.create') }}" class="btn-primary">
        + Thêm giai đoạn
    </a>
@endsection

@section('content')

    {{-- Bộ lọc --}}
    <div class="card mb-5">
        <form action="{{ route('admin.stages.index') }}" method="GET"
              class="flex flex-col lg:flex-row lg:items-center gap-3">

            <div class="flex-1">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Tìm kiếm tên hoặc mô tả giai đoạn..."
                    class="w-full border border-admin-border rounded-xl px-4 py-2.5 text-sm
                           text-ink outline-none focus:border-coral focus:ring-1 focus:ring-coral"
                >
            </div>

            <div class="w-full lg:w-52">
                <select
                    name="status"
                    class="w-full border border-admin-border rounded-xl px-4 py-2.5 text-sm
                           text-ink outline-none focus:border-coral"
                >
                    <option value="">Tất cả trạng thái</option>

                    <option value="active" @selected(request('status') === 'active')>
                        Đang hoạt động
                    </option>

                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Đã ẩn
                    </option>
                </select>
            </div>

            <button
                type="submit"
                class="px-5 py-2.5 rounded-xl bg-coral text-white text-sm font-semibold
                       hover:opacity-90 transition"
            >
                Lọc
            </button>

            @if (request()->filled('search') || request()->filled('status'))
                <a
                    href="{{ route('admin.stages.index') }}"
                    class="px-5 py-2.5 rounded-xl border border-admin-border text-sm text-ink-soft
                           hover:bg-admin-bg transition text-center"
                >
                    Làm mới
                </a>
            @endif
        </form>
    </div>

    {{-- Bảng dữ liệu --}}
    <div class="card overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead class="bg-admin-bg text-ink-soft text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold w-16">
                            STT
                        </th>

                        <th class="text-left px-5 py-3 font-semibold">
                            Giai đoạn
                        </th>

                        <th class="text-left px-5 py-3 font-semibold">
                            Độ tuổi
                        </th>

                        <th class="text-left px-5 py-3 font-semibold">
                            Mô tả
                        </th>

                        <th class="text-center px-5 py-3 font-semibold">
                            Thứ tự
                        </th>

                        <th class="text-left px-5 py-3 font-semibold">
                            Trạng thái
                        </th>

                        <th class="text-right px-5 py-3 font-semibold">
                            Thao tác
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-admin-border">

                    @forelse ($stages as $stage)

                        <tr class="hover:bg-admin-bg/60 transition">

                            {{-- STT --}}
                            <td class="px-5 py-4 text-ink-soft">
                                {{ $stages->firstItem() + $loop->index }}
                            </td>

                            {{-- Tên --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">

                                    <div class="w-10 h-10 rounded-xl bg-coral-light
                                                text-coral flex items-center justify-center
                                                text-lg font-semibold shrink-0">
                                        @if ($stage->icon)
                                            {{ $stage->icon }}
                                        @else
                                            {{ strtoupper(mb_substr($stage->name, 0, 1)) }}
                                        @endif
                                    </div>

                                    <div>
                                        <p class="font-semibold text-ink">
                                            {{ $stage->name }}
                                        </p>

                                        <p class="text-xs text-ink-soft mt-0.5">
                                            ID #{{ $stage->id }}
                                        </p>
                                    </div>

                                </div>
                            </td>

                            {{-- Độ tuổi --}}
                            <td class="px-5 py-4 text-ink">

                                @if (!is_null($stage->age_from) && !is_null($stage->age_to))
                                    {{ $stage->age_from }} - {{ $stage->age_to }} tháng

                                @elseif (!is_null($stage->age_from))
                                    Từ {{ $stage->age_from }} tháng

                                @elseif (!is_null($stage->age_to))
                                    Đến {{ $stage->age_to }} tháng

                                @else
                                    <span class="text-ink-soft">Chưa xác định</span>
                                @endif

                            </td>

                            {{-- Mô tả --}}
                            <td class="px-5 py-4 text-ink-soft max-w-xs">
                                <p class="line-clamp-2">
                                    {{ $stage->description ?: 'Chưa có mô tả' }}
                                </p>
                            </td>

                            {{-- Sort --}}
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex min-w-8 h-8 items-center justify-center
                                             rounded-lg bg-admin-bg text-ink font-medium">
                                    {{ $stage->sort_order }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">

                                @if ($stage->is_active)
                                    <span class="inline-flex items-center gap-1.5
                                                 text-green-600 text-sm font-medium">
                                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                        Đang hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5
                                                 text-ink-soft text-sm font-medium">
                                        <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                                        Đã ẩn
                                    </span>
                                @endif

                            </td>

                            {{-- Action --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    <a
                                        href="{{ route('admin.stages.edit', $stage) }}"
                                        class="inline-flex items-center justify-center
                                               px-3 py-1.5 rounded-lg border border-admin-border
                                               text-sm text-ink hover:border-coral hover:text-coral
                                               transition"
                                    >
                                        Sửa
                                    </a>

                                    <button
                                        type="button"
                                        data-action="{{ route('admin.stages.destroy', $stage) }}"
                                        data-name="{{ $stage->name }}"
                                        onclick="openDeleteStageModal(this)"
                                        class="inline-flex items-center justify-center
                                               px-3 py-1.5 rounded-lg
                                               bg-red-50 text-red-500
                                               hover:bg-red-100 transition"
                                    >
                                        Xóa
                                    </button>

                                </div>

                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="7"
                                class="px-5 py-14 text-center">

                                <div class="text-4xl mb-3">
                                    👶
                                </div>

                                <p class="font-semibold text-ink">
                                    Chưa có giai đoạn nào
                                </p>

                                <p class="text-sm text-ink-soft mt-1">
                                    Hãy tạo giai đoạn đầu tiên cho mẹ và bé.
                                </p>

                                <a
                                    href="{{ route('admin.stages.create') }}"
                                    class="btn-primary inline-block mt-4"
                                >
                                    + Thêm giai đoạn
                                </a>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{-- Pagination --}}
        @if ($stages->hasPages())
            <div class="border-t border-admin-border px-5 py-4">
                {{ $stages->links() }}
            </div>
        @endif

    </div>


    {{-- Modal xác nhận xóa --}}
    <div
        id="deleteStageModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4"
    >
        <div
            class="w-full max-w-md rounded-2xl bg-white shadow-2xl"
            onclick="event.stopPropagation()"
        >
            <div class="p-6">

                <div class="flex items-start gap-4">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center
                                rounded-full bg-red-50 text-red-500 text-xl">
                        !
                    </div>

                    <div class="flex-1">

                        <h3 class="text-lg font-semibold text-ink">
                            Xóa giai đoạn?
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-ink-soft">
                            Bạn có chắc muốn xóa
                            <span
                                id="deleteStageName"
                                class="font-semibold text-ink"
                            ></span>?
                        </p>

                        <p class="mt-1 text-xs text-red-500">
                            Thao tác này không thể hoàn tác.
                        </p>

                    </div>

                </div>

                <div class="mt-6 flex justify-end gap-3">

                    <button
                        type="button"
                        onclick="closeDeleteStageModal()"
                        class="rounded-xl border border-admin-border
                               px-4 py-2.5 text-sm font-medium text-ink
                               hover:bg-admin-bg transition"
                    >
                        Hủy
                    </button>

                    <form
                        id="deleteStageForm"
                        method="POST"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="rounded-xl bg-red-500 px-4 py-2.5
                                   text-sm font-semibold text-white
                                   hover:bg-red-600 transition"
                        >
                            Xóa giai đoạn
                        </button>
                    </form>

                </div>

            </div>
        </div>
    </div>

@endsection


@push('scripts')
<script>
    function openDeleteStageModal(button) {
        const modal = document.getElementById('deleteStageModal');
        const form = document.getElementById('deleteStageForm');
        const name = document.getElementById('deleteStageName');

        form.action = button.dataset.action;
        name.textContent = button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }

    function closeDeleteStageModal() {
        const modal = document.getElementById('deleteStageModal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDeleteStageModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('deleteStageModal');

        if (!modal) {
            return;
        }

        modal.addEventListener('click', function () {
            closeDeleteStageModal();
        });
    });
</script>
@endpush