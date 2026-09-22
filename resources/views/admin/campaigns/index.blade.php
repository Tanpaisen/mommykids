@extends('admin.layouts.app')

@section('page_title', 'Chiến dịch khuyến mãi')
@section('page_subtitle', 'Quản lý Flash Sale và các chương trình giảm giá theo sản phẩm')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
    <div>
        <h2 class="text-lg font-semibold text-ink">
            Danh sách chiến dịch
        </h2>

        <p class="text-sm text-ink-soft mt-1">
            Tạo và quản lý giá khuyến mãi, thời gian chạy, priority và quota sản phẩm.
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <a
            href="{{ route('admin.campaigns.trash') }}"
            class="inline-flex items-center gap-2 h-11 px-4 rounded-xl border border-admin-border bg-white text-sm font-medium text-ink hover:border-red-300 hover:text-red-500 transition"
        >
            <span>🗑️</span>
            <span>Thùng rác</span>

            @if (($trashCount ?? 0) > 0)
                <span class="inline-flex min-w-5 h-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-semibold text-white">
                    {{ $trashCount }}
                </span>
            @endif
        </a>

        <a
            href="{{ route('admin.campaigns.create') }}"
            class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-xl bg-coral text-white text-sm font-semibold hover:opacity-90 transition"
        >
            <span>+</span>
            <span>Thêm chiến dịch</span>
        </a>
    </div>
</div>

@if (session('success'))
    <div class="card mb-5 border border-green-200 bg-green-50 text-green-700">
        {{ session('success') }}
    </div>
@endif

<div class="card mb-5">
    <form
        method="GET"
        action="{{ route('admin.campaigns.index') }}"
        class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-[2fr_1fr_1fr_auto] gap-3"
    >
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Tìm theo tên chiến dịch..."
            class="border border-admin-border rounded-xl px-4 py-3 bg-white outline-none focus:border-coral focus:ring-2 focus:ring-coral/10"
        >

        <select
            name="type"
            class="border border-admin-border rounded-xl px-4 py-3 bg-white outline-none focus:border-coral focus:ring-2 focus:ring-coral/10"
        >
            <option value="">Tất cả loại</option>

            @foreach ($types as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(request('type') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select
            name="status"
            class="border border-admin-border rounded-xl px-4 py-3 bg-white outline-none focus:border-coral focus:ring-2 focus:ring-coral/10"
        >
            <option value="">Tất cả trạng thái</option>
            <option value="active" @selected(request('status') === 'active')>
                Đang chạy
            </option>
            <option value="scheduled" @selected(request('status') === 'scheduled')>
                Sắp diễn ra
            </option>
            <option value="ended" @selected(request('status') === 'ended')>
                Đã kết thúc
            </option>
            <option value="inactive" @selected(request('status') === 'inactive')>
                Đã tắt
            </option>
        </select>

        <button
            type="submit"
            class="bg-coral text-white rounded-xl px-6 py-3 font-semibold hover:opacity-90 transition"
        >
            Lọc
        </button>
    </form>

    @if (
        request()->filled('search')
        || request()->filled('type')
        || request()->filled('status')
    )
        <div class="mt-3">
            <a
                href="{{ route('admin.campaigns.index') }}"
                class="inline-flex items-center gap-1 text-sm text-ink-soft hover:text-coral transition"
            >
                ↻ Làm mới bộ lọc
            </a>
        </div>
    @endif
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1050px] text-sm">
            <thead class="bg-admin-bg text-xs uppercase text-ink-soft">
                <tr>
                    <th class="text-left px-5 py-4">STT</th>
                    <th class="text-left px-5 py-4 min-w-[260px]">Chiến dịch</th>
                    <th class="text-left px-5 py-4">Loại</th>
                    <th class="text-center px-5 py-4">Sản phẩm</th>
                    <th class="text-center px-5 py-4">Priority</th>
                    <th class="text-left px-5 py-4 min-w-[240px]">Thời gian</th>
                    <th class="text-left px-5 py-4">Trạng thái</th>
                    <th class="text-right px-5 py-4">Thao tác</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-admin-border">
                @forelse ($campaigns as $campaign)
                    @php
                        $now = now();

                        if (!$campaign->is_active) {
                            $statusText = 'Đã tắt';
                            $statusClass = 'bg-gray-100 text-gray-500';
                        } elseif ($campaign->starts_at && $campaign->starts_at->gt($now)) {
                            $statusText = 'Sắp diễn ra';
                            $statusClass = 'bg-blue-50 text-blue-600';
                        } elseif ($campaign->ends_at && $campaign->ends_at->lt($now)) {
                            $statusText = 'Đã kết thúc';
                            $statusClass = 'bg-red-50 text-red-500';
                        } else {
                            $statusText = 'Đang chạy';
                            $statusClass = 'bg-green-50 text-green-600';
                        }
                    @endphp

                    <tr class="hover:bg-admin-bg/40 transition">
                        <td class="px-5 py-4 text-ink-soft">
                            {{ $campaigns->firstItem() + $loop->index }}
                        </td>

                        <td class="px-5 py-4">
                            <p class="font-semibold text-ink">
                                {{ $campaign->name }}
                            </p>

                            <p class="text-xs text-ink-soft mt-1">
                                ID #{{ $campaign->id }}
                            </p>

                            @if ($campaign->description)
                                <p class="text-xs text-ink-soft mt-1 max-w-[320px] truncate">
                                    {{ $campaign->description }}
                                </p>
                            @endif
                        </td>

                        <td class="px-5 py-4">
                            <span class="inline-flex rounded-lg bg-coral/10 text-coral px-2.5 py-1 text-xs font-semibold">
                                {{ $types[$campaign->type] ?? $campaign->type }}
                            </span>
                        </td>

                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex min-w-10 h-9 items-center justify-center rounded-xl bg-admin-bg px-2 font-semibold">
                                {{ $campaign->products_count }}
                            </span>
                        </td>

                        <td class="px-5 py-4 text-center">
                            <span class="font-semibold text-ink">
                                {{ $campaign->priority }}
                            </span>
                        </td>

                        <td class="px-5 py-4">
                            <p class="text-xs text-ink">
                                Bắt đầu:
                                <strong>
                                    {{ $campaign->starts_at?->format('d/m/Y H:i') ?? 'Ngay lập tức' }}
                                </strong>
                            </p>

                            <p class="text-xs text-ink-soft mt-1">
                                Kết thúc:
                                <strong>
                                    {{ $campaign->ends_at?->format('d/m/Y H:i') ?? 'Không giới hạn' }}
                                </strong>
                            </p>
                        </td>

                        <td class="px-5 py-4">
                            <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a
                                    href="{{ route('admin.campaigns.edit', $campaign) }}"
                                    class="px-4 py-2 border border-admin-border rounded-lg text-ink hover:border-coral hover:text-coral transition"
                                >
                                    Sửa
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.campaigns.destroy', $campaign) }}"
                                    onsubmit="return confirm('Chuyển chiến dịch này vào thùng rác?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="px-4 py-2 bg-red-50 text-red-500 rounded-lg hover:bg-red-100 transition"
                                    >
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-20 text-center">
                            <div class="text-4xl">🏷️</div>

                            <p class="font-semibold mt-3 text-ink">
                                Chưa có chiến dịch
                            </p>

                            <p class="text-sm text-ink-soft mt-1">
                                Hãy tạo chiến dịch đầu tiên để áp dụng giá khuyến mãi.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($campaigns->hasPages())
        <div class="border-t border-admin-border px-5 py-4">
            {{ $campaigns->links() }}
        </div>
    @endif
</div>

@endsection
