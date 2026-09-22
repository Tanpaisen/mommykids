@extends('admin.layouts.app')

@section('page_title', 'Thùng rác chiến dịch')
@section('page_subtitle', 'Khôi phục hoặc xóa vĩnh viễn chiến dịch khuyến mãi')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
    <div>
        <h2 class="text-lg font-semibold text-ink">
            Chiến dịch đã xóa
        </h2>

        <p class="text-sm text-ink-soft mt-1">
            Các chiến dịch xóa mềm vẫn có thể khôi phục.
        </p>
    </div>

    <a
        href="{{ route('admin.campaigns.index') }}"
        class="inline-flex items-center justify-center h-11 px-5 rounded-xl border border-admin-border bg-white text-sm font-semibold text-ink hover:border-coral hover:text-coral transition"
    >
        ← Danh sách chiến dịch
    </a>
</div>

@if (session('success'))
    <div class="card mb-5 border border-green-200 bg-green-50 text-green-700">
        {{ session('success') }}
    </div>
@endif

<div class="card mb-5">
    <form
        method="GET"
        action="{{ route('admin.campaigns.trash') }}"
        class="flex flex-col sm:flex-row gap-3"
    >
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Tìm tên chiến dịch..."
            class="flex-1 border border-admin-border rounded-xl px-4 py-3 bg-white outline-none focus:border-coral focus:ring-2 focus:ring-coral/10"
        >

        <button
            type="submit"
            class="bg-coral text-white rounded-xl px-6 py-3 font-semibold hover:opacity-90 transition"
        >
            Tìm
        </button>
    </form>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-sm">
            <thead class="bg-admin-bg text-xs uppercase text-ink-soft">
                <tr>
                    <th class="text-left px-5 py-4">STT</th>
                    <th class="text-left px-5 py-4">Chiến dịch</th>
                    <th class="text-left px-5 py-4">Loại</th>
                    <th class="text-center px-5 py-4">Sản phẩm</th>
                    <th class="text-left px-5 py-4">Ngày xóa</th>
                    <th class="text-right px-5 py-4">Thao tác</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-admin-border">
                @forelse ($campaigns as $campaign)
                    <tr>
                        <td class="px-5 py-4 text-ink-soft">
                            {{ $campaigns->firstItem() + $loop->index }}
                        </td>

                        <td class="px-5 py-4 font-semibold text-ink">
                            {{ $campaign->name }}
                        </td>

                        <td class="px-5 py-4">
                            {{ $types[$campaign->type] ?? $campaign->type }}
                        </td>

                        <td class="px-5 py-4 text-center">
                            {{ $campaign->products_count }}
                        </td>

                        <td class="px-5 py-4 text-ink-soft">
                            {{ $campaign->deleted_at?->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <form
                                    method="POST"
                                    action="{{ route('admin.campaigns.restore', $campaign->id) }}"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="px-4 py-2 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition"
                                    >
                                        Khôi phục
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route('admin.campaigns.force-delete', $campaign->id) }}"
                                    onsubmit="return confirm('Xóa vĩnh viễn chiến dịch này?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="px-4 py-2 bg-red-50 text-red-500 rounded-lg hover:bg-red-100 transition"
                                    >
                                        Xóa vĩnh viễn
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center text-ink-soft">
                            Thùng rác đang trống.
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
