@extends('admin.layouts.app')

@section('title', 'Chăm sóc khách hàng')

@section('content')
<div class="p-6 space-y-6">

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-2xl font-bold text-ink">
                Chăm sóc khách hàng
            </h1>

            <p class="mt-1 text-sm text-ink-soft">
                Quản lý kịch bản Chat Bot và tiếp nhận tư vấn trực tiếp từ khách hàng.
            </p>
        </div>

        <a
            href="{{ route('admin.chats.index') }}"
            class="inline-flex items-center justify-center
                   rounded-xl border border-admin-border
                   bg-white px-4 py-2.5
                   text-sm font-semibold text-ink
                   hover:bg-gray-50 transition"
        >
            Tất cả hội thoại
        </a>

    </div>


    {{-- =========================================================
        FLASH MESSAGE
    ========================================================== --}}
    @if(session('success'))
        <div class="rounded-xl border border-green-200
                    bg-green-50 px-4 py-3
                    text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif


    {{-- =========================================================
        STATS
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-admin-border bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">
                Kịch bản Chat Bot
            </p>

            <p class="mt-3 text-2xl font-bold text-ink">
                {{ $stats['scenario_total'] }}
            </p>

            <p class="mt-1 text-sm text-ink-soft">
                {{ $stats['scenario_active'] }} đang hoạt động
            </p>
        </div>


        <div class="rounded-2xl border border-admin-border bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">
                Chờ tiếp nhận
            </p>

            <p class="mt-3 text-2xl font-bold text-ink">
                {{ $stats['waiting'] }}
            </p>

            <p class="mt-1 text-sm text-ink-soft">
                Khách đang chờ nhân viên
            </p>
        </div>


        <div class="rounded-2xl border border-admin-border bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">
                Đang tư vấn
            </p>

            <p class="mt-3 text-2xl font-bold text-ink">
                {{ $stats['connected'] }}
            </p>

            <p class="mt-1 text-sm text-ink-soft">
                {{ $stats['mine'] }} phiên của bạn
            </p>
        </div>


        <div class="rounded-2xl border border-admin-border bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">
                Kết thúc hôm nay
            </p>

            <p class="mt-3 text-2xl font-bold text-ink">
                {{ $stats['closed_today'] }}
            </p>

            <p class="mt-1 text-sm text-ink-soft">
                Phiên đã hoàn thành
            </p>
        </div>

    </div>


    {{-- =========================================================
        TABS
    ========================================================== --}}
    <div class="inline-flex rounded-xl border border-admin-border bg-white p-1">

        <a
            href="{{ route('admin.customer-care.index', ['tab' => 'scenarios']) }}"
            class="rounded-lg px-5 py-2.5 text-sm font-semibold transition
                   {{ $tab === 'scenarios'
                        ? 'bg-coral text-white'
                        : 'text-ink-soft hover:bg-gray-50 hover:text-ink' }}"
        >
            Kịch bản Chat Bot
        </a>

        <a
            href="{{ route('admin.customer-care.index', ['tab' => 'live']) }}"
            class="rounded-lg px-5 py-2.5 text-sm font-semibold transition
                   {{ $tab === 'live'
                        ? 'bg-coral text-white'
                        : 'text-ink-soft hover:bg-gray-50 hover:text-ink' }}"
        >
            Tiếp nhận tư vấn trực tiếp

            @if($stats['waiting'] > 0)
                <span
                    class="ml-1 inline-flex min-w-5 items-center justify-center
                           rounded-full bg-red-500 px-1.5
                           text-[10px] font-bold text-white"
                >
                    {{ $stats['waiting'] }}
                </span>
            @endif
        </a>

    </div>


    {{-- =========================================================
        TAB: LIVE CONSULTATION
    ========================================================== --}}
    @if($tab === 'live')

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

            {{-- Chờ tiếp nhận --}}
            <section class="overflow-hidden rounded-2xl border border-admin-border bg-white">

                <div class="flex items-center justify-between
                            border-b border-admin-border
                            px-5 py-4">

                    <div>
                        <h2 class="font-semibold text-ink">
                            Chờ tiếp nhận
                        </h2>

                        <p class="mt-1 text-xs text-ink-soft">
                            Khách đang yêu cầu hỗ trợ từ nhân viên.
                        </p>
                    </div>

                    <span class="rounded-full bg-orange-50
                                 px-3 py-1
                                 text-xs font-semibold text-orange-600">
                        {{ $waitingConversations->count() }}
                    </span>

                </div>


                <div class="divide-y divide-admin-border">

                    @forelse($waitingConversations as $conversation)

                        <a
                            href="{{ route('admin.chats.show', $conversation) }}"
                            class="flex items-center justify-between gap-4
                                   px-5 py-4
                                   hover:bg-gray-50 transition"
                        >

                            <div class="min-w-0">

                                <p class="truncate text-sm font-semibold text-ink">
                                    {{ $conversation->user?->name ?? 'Khách vãng lai' }}
                                </p>

                                @if($conversation->user?->email)
                                    <p class="mt-1 truncate text-xs text-ink-soft">
                                        {{ $conversation->user->email }}
                                    </p>
                                @endif

                                <div class="mt-2 flex flex-wrap gap-3 text-xs text-ink-soft">

                                    <span>
                                        {{ $conversation->messages_count }} tin nhắn
                                    </span>

                                    <span>
                                        {{ $conversation->last_message_at?->format('H:i d/m/Y') }}
                                    </span>

                                </div>

                            </div>


                            <span class="shrink-0 rounded-full
                                         bg-orange-50 px-3 py-1.5
                                         text-xs font-semibold text-orange-600">
                                Chờ xử lý
                            </span>

                        </a>

                    @empty

                        <div class="px-5 py-12 text-center">
                            <p class="text-sm font-medium text-ink">
                                Không có khách đang chờ
                            </p>

                            <p class="mt-1 text-xs text-ink-soft">
                                Các yêu cầu mới sẽ xuất hiện tại đây.
                            </p>
                        </div>

                    @endforelse

                </div>

            </section>


            {{-- Phiên của tôi --}}
            <section class="overflow-hidden rounded-2xl border border-admin-border bg-white">

                <div class="flex items-center justify-between
                            border-b border-admin-border
                            px-5 py-4">

                    <div>
                        <h2 class="font-semibold text-ink">
                            Phiên của tôi
                        </h2>

                        <p class="mt-1 text-xs text-ink-soft">
                            Những khách hàng bạn đang trực tiếp tư vấn.
                        </p>
                    </div>

                    <span class="rounded-full bg-blue-50
                                 px-3 py-1
                                 text-xs font-semibold text-blue-600">
                        {{ $myConversations->count() }}
                    </span>

                </div>


                <div class="divide-y divide-admin-border">

                    @forelse($myConversations as $conversation)

                        <a
                            href="{{ route('admin.chats.show', $conversation) }}"
                            class="flex items-center justify-between gap-4
                                   px-5 py-4
                                   hover:bg-gray-50 transition"
                        >

                            <div class="min-w-0">

                                <p class="truncate text-sm font-semibold text-ink">
                                    {{ $conversation->user?->name ?? 'Khách vãng lai' }}
                                </p>

                                @if($conversation->user?->email)
                                    <p class="mt-1 truncate text-xs text-ink-soft">
                                        {{ $conversation->user->email }}
                                    </p>
                                @endif

                                <div class="mt-2 flex flex-wrap gap-3 text-xs text-ink-soft">

                                    <span>
                                        {{ $conversation->messages_count }} tin nhắn
                                    </span>

                                    <span>
                                        {{ $conversation->last_message_at?->format('H:i d/m/Y') }}
                                    </span>

                                </div>

                            </div>


                            <span class="shrink-0 rounded-full
                                         bg-blue-50 px-3 py-1.5
                                         text-xs font-semibold text-blue-600">
                                Đang tư vấn
                            </span>

                        </a>

                    @empty

                        <div class="px-5 py-12 text-center">
                            <p class="text-sm font-medium text-ink">
                                Chưa có phiên tư vấn
                            </p>

                            <p class="mt-1 text-xs text-ink-soft">
                                Phiên bạn tiếp nhận sẽ xuất hiện tại đây.
                            </p>
                        </div>

                    @endforelse

                </div>

            </section>

        </div>


    {{-- =========================================================
        TAB: BOT SCENARIOS
    ========================================================== --}}
    @else

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[340px_minmax(0,1fr)]">

            {{-- CREATE --}}
            <section class="self-start rounded-2xl border border-admin-border bg-white p-5">

                <div class="mb-5">
                    <h2 class="font-semibold text-ink">
                        Tạo kịch bản mới
                    </h2>

                    <p class="mt-1 text-xs leading-5 text-ink-soft">
                        Bot sẽ phản hồi khi tin nhắn của khách khớp với một trong các từ khóa.
                    </p>
                </div>


                <form
                    method="POST"
                    action="{{ route('admin.customer-care.scenarios.store') }}"
                    class="space-y-4"
                >
                    @csrf


                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-ink">
                            Tên kịch bản
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            maxlength="150"
                            placeholder="Ví dụ: Hỏi phí vận chuyển"
                            class="w-full rounded-xl
                                   border border-admin-border
                                   bg-white px-3.5 py-2.5
                                   text-sm text-ink
                                   outline-none
                                   focus:border-coral"
                        >
                    </div>


                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-ink">
                            Từ khóa
                        </label>

                        <textarea
                            name="keywords_text"
                            rows="4"
                            required
                            placeholder="phí ship, vận chuyển, giao hàng, GHN"
                            class="w-full resize-none rounded-xl
                                   border border-admin-border
                                   bg-white px-3.5 py-2.5
                                   text-sm text-ink
                                   outline-none
                                   focus:border-coral"
                        >{{ old('keywords_text') }}</textarea>

                        <p class="mt-1 text-[11px] text-ink-soft">
                            Ngăn cách từ khóa bằng dấu phẩy hoặc xuống dòng.
                        </p>
                    </div>


                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-ink">
                            Nội dung Bot trả lời
                        </label>

                        <textarea
                            name="response"
                            rows="5"
                            required
                            placeholder="Nhập nội dung phản hồi..."
                            class="w-full resize-none rounded-xl
                                   border border-admin-border
                                   bg-white px-3.5 py-2.5
                                   text-sm text-ink
                                   outline-none
                                   focus:border-coral"
                        >{{ old('response') }}</textarea>
                    </div>


                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-ink">
                            Độ ưu tiên
                        </label>

                        <input
                            type="number"
                            name="priority"
                            value="{{ old('priority', 100) }}"
                            min="1"
                            max="9999"
                            required
                            class="w-full rounded-xl
                                   border border-admin-border
                                   bg-white px-3.5 py-2.5
                                   text-sm text-ink
                                   outline-none
                                   focus:border-coral"
                        >
                    </div>


                    <div class="space-y-2">

                        <label class="flex items-center gap-2 text-sm text-ink">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                checked
                                class="rounded border-admin-border text-coral"
                            >
                            Bật kịch bản
                        </label>


                        <label class="flex items-center gap-2 text-sm text-ink">
                            <input
                                type="checkbox"
                                name="handoff_to_staff"
                                value="1"
                                class="rounded border-admin-border text-coral"
                            >
                            Chuyển sang nhân viên
                        </label>

                    </div>


                    <button
                        type="submit"
                        class="w-full rounded-xl bg-coral
                               px-4 py-2.5
                               text-sm font-semibold text-white
                               hover:opacity-90 transition"
                    >
                        Lưu kịch bản
                    </button>

                </form>

            </section>


            {{-- LIST --}}
            <section class="overflow-hidden rounded-2xl border border-admin-border bg-white">

                <div class="border-b border-admin-border px-5 py-4">
                    <h2 class="font-semibold text-ink">
                        Danh sách kịch bản
                    </h2>

                    <p class="mt-1 text-xs text-ink-soft">
                        {{ $stats['scenario_active'] }} / {{ $stats['scenario_total'] }}
                        kịch bản đang hoạt động.
                    </p>
                </div>


                <div class="divide-y divide-admin-border">

                    @forelse($scenarios as $scenario)

                        <div class="p-5">

                            <div class="flex flex-col gap-4
                                        lg:flex-row lg:items-start lg:justify-between">

                                <div class="min-w-0">

                                    <div class="flex flex-wrap items-center gap-2">

                                        <h3 class="text-sm font-semibold text-ink">
                                            {{ $scenario->name }}
                                        </h3>

                                        @if($scenario->is_active)
                                            <span class="rounded-full bg-green-50
                                                         px-2 py-1
                                                         text-[10px] font-semibold text-green-600">
                                                Đang bật
                                            </span>
                                        @else
                                            <span class="rounded-full bg-gray-100
                                                         px-2 py-1
                                                         text-[10px] font-semibold text-gray-500">
                                                Đang tắt
                                            </span>
                                        @endif


                                        @if($scenario->handoff_to_staff)
                                            <span class="rounded-full bg-orange-50
                                                         px-2 py-1
                                                         text-[10px] font-semibold text-orange-600">
                                                Chuyển nhân viên
                                            </span>
                                        @endif

                                    </div>


                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1
                                                text-xs text-ink-soft">

                                        <span>
                                            Ưu tiên: {{ $scenario->priority }}
                                        </span>

                                        <span>
                                            Đã khớp: {{ number_format($scenario->matched_count) }}
                                        </span>

                                    </div>


                                    <p class="mt-2 text-xs leading-5 text-ink-soft">
                                        {{ implode(', ', array_slice($scenario->keywords ?? [], 0, 7)) }}
                                    </p>

                                </div>


                                <div class="flex shrink-0 gap-2">

                                    <form
                                        method="POST"
                                        action="{{ route('admin.customer-care.scenarios.toggle', $scenario) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="rounded-lg border border-admin-border
                                                   bg-white px-3 py-2
                                                   text-xs font-semibold text-ink
                                                   hover:bg-gray-50 transition"
                                        >
                                            {{ $scenario->is_active ? 'Tắt' : 'Bật' }}
                                        </button>
                                    </form>


                                    <form
                                        method="POST"
                                        action="{{ route('admin.customer-care.scenarios.destroy', $scenario) }}"
                                        onsubmit="return confirm('Bạn có chắc muốn xóa kịch bản này?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg border border-red-200
                                                   bg-white px-3 py-2
                                                   text-xs font-semibold text-red-500
                                                   hover:bg-red-50 transition"
                                        >
                                            Xóa
                                        </button>
                                    </form>

                                </div>

                            </div>


                            <details class="mt-4">

                                <summary
                                    class="cursor-pointer
                                           text-xs font-semibold text-ink-soft
                                           hover:text-coral"
                                >
                                    Xem và chỉnh sửa
                                </summary>


                                <form
                                    method="POST"
                                    action="{{ route('admin.customer-care.scenarios.update', $scenario) }}"
                                    class="mt-4 grid grid-cols-1 gap-4
                                           rounded-xl bg-gray-50 p-4
                                           md:grid-cols-2"
                                >
                                    @csrf
                                    @method('PUT')


                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-ink">
                                            Tên kịch bản
                                        </label>

                                        <input
                                            name="name"
                                            value="{{ $scenario->name }}"
                                            required
                                            class="w-full rounded-lg
                                                   border border-admin-border
                                                   bg-white px-3 py-2
                                                   text-sm"
                                        >
                                    </div>


                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-ink">
                                            Độ ưu tiên
                                        </label>

                                        <input
                                            type="number"
                                            name="priority"
                                            value="{{ $scenario->priority }}"
                                            min="1"
                                            max="9999"
                                            required
                                            class="w-full rounded-lg
                                                   border border-admin-border
                                                   bg-white px-3 py-2
                                                   text-sm"
                                        >
                                    </div>


                                    <div class="md:col-span-2">
                                        <label class="mb-1.5 block text-xs font-semibold text-ink">
                                            Từ khóa
                                        </label>

                                        <textarea
                                            name="keywords_text"
                                            rows="3"
                                            required
                                            class="w-full resize-none rounded-lg
                                                   border border-admin-border
                                                   bg-white px-3 py-2
                                                   text-sm"
                                        >{{ implode(', ', $scenario->keywords ?? []) }}</textarea>
                                    </div>


                                    <div class="md:col-span-2">
                                        <label class="mb-1.5 block text-xs font-semibold text-ink">
                                            Nội dung trả lời
                                        </label>

                                        <textarea
                                            name="response"
                                            rows="4"
                                            required
                                            class="w-full resize-none rounded-lg
                                                   border border-admin-border
                                                   bg-white px-3 py-2
                                                   text-sm"
                                        >{{ $scenario->response }}</textarea>
                                    </div>


                                    <div class="md:col-span-2 flex flex-wrap gap-5">

                                        <label class="flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                name="is_active"
                                                value="1"
                                                {{ $scenario->is_active ? 'checked' : '' }}
                                            >
                                            Đang hoạt động
                                        </label>


                                        <label class="flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                name="handoff_to_staff"
                                                value="1"
                                                {{ $scenario->handoff_to_staff ? 'checked' : '' }}
                                            >
                                            Chuyển sang nhân viên
                                        </label>

                                    </div>


                                    <div class="md:col-span-2">

                                        <button
                                            type="submit"
                                            class="rounded-lg bg-ink
                                                   px-4 py-2.5
                                                   text-xs font-semibold text-white"
                                        >
                                            Lưu thay đổi
                                        </button>

                                    </div>

                                </form>

                            </details>

                        </div>

                    @empty

                        <div class="px-5 py-14 text-center">
                            <p class="text-sm font-medium text-ink">
                                Chưa có kịch bản Chat Bot
                            </p>

                            <p class="mt-1 text-xs text-ink-soft">
                                Tạo kịch bản đầu tiên bằng biểu mẫu bên trái.
                            </p>
                        </div>

                    @endforelse

                </div>

            </section>

        </div>

    @endif

</div>
@endsection