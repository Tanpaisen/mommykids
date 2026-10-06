@extends('client.layouts.app')
@section('sidebar')
    <div class="hidden"></div>
@endsection

@section('title', 'Kho voucher của tôi - MommyKids')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            Kho voucher của tôi
        </h1>

        <p class="text-gray-500 mt-2">
            Quản lý các mã ưu đãi bạn đã lưu tại MommyKids
        </p>
    </div>
    <div class="mb-6">
    <a
        href="{{ route('vouchers.index') }}"
        class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-pink-500 transition"
    >
        ← Quay lại khuyến mãi
    </a>
</div>

    {{-- Tabs --}}
    <div class="flex gap-3 mb-8 border-b border-gray-200 pb-4">
        <button
            type="button"
            class="voucher-tab px-5 py-2 rounded-full bg-pink-500 text-white font-semibold"
            data-tab="available"
        >
            Có thể dùng ({{ $availableVouchers->count() }})
        </button>

        <button
            type="button"
            class="voucher-tab px-5 py-2 rounded-full bg-gray-100 text-gray-600 font-semibold"
            data-tab="used"
        >
            Đã sử dụng ({{ $usedVouchers->count() }})
        </button>

        <button
            type="button"
            class="voucher-tab px-5 py-2 rounded-full bg-gray-100 text-gray-600 font-semibold"
            data-tab="expired"
        >
            Hết hạn ({{ $expiredVouchers->count() }})
        </button>
    </div>

    {{-- Voucher còn dùng được --}}
    <div id="tab-available" class="voucher-tab-content">

        @if($availableVouchers->isEmpty())

            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center shadow-sm">
                <div class="text-5xl mb-4">
                    🎟️
                </div>

                <h3 class="text-lg font-bold text-gray-700">
                    Bạn chưa có voucher có thể sử dụng
                </h3>

                <p class="text-gray-500 mt-2">
                    Hãy săn thêm voucher để nhận ưu đãi nhé.
                </p>

                <a
                    href="{{ route('vouchers.index') }}"
                    class="inline-block mt-5 bg-pink-500 hover:bg-pink-600 text-white font-semibold px-6 py-3 rounded-xl"
                >
                    Săn voucher
                </a>
            </div>

        @else

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                @foreach($availableVouchers as $voucher)

                    <div class="bg-white rounded-2xl border border-pink-100 shadow-sm overflow-hidden">

                        <div class="flex">

                            <div class="w-32 bg-pink-500 text-white flex flex-col items-center justify-center p-4">

                                @if($voucher->discount_type === 'percent')

                                    <div class="text-2xl font-black">
                                        {{ $voucher->discount_value }}%
                                    </div>

                                    <div class="text-xs mt-1">
                                        GIẢM
                                    </div>

                                @elseif($voucher->discount_type === 'free_shipping')

                                    <div class="text-2xl">
                                        🚚
                                    </div>

                                    <div class="font-bold mt-1">
                                        FREESHIP
                                    </div>

                                @else

                                    <div class="text-xl font-black">
                                        {{ number_format($voucher->discount_value, 0, ',', '.') }}đ
                                    </div>

                                    <div class="text-xs mt-1">
                                        GIẢM
                                    </div>

                                @endif

                            </div>

                            <div class="flex-1 p-5">

                                <div class="flex justify-between gap-4">

                                    <div>

                                        <h3 class="font-bold text-gray-800">
                                            {{ $voucher->name }}
                                        </h3>

                                        <p class="text-sm text-gray-500 mt-1">
                                            Mã:
                                            <span class="font-bold text-pink-500">
                                                {{ $voucher->code }}
                                            </span>
                                        </p>

                                    </div>

                                    <span class="text-xs bg-green-50 text-green-600 px-3 py-1 rounded-full h-fit font-semibold">
                                        Có thể dùng
                                    </span>

                                </div>

                                @if($voucher->min_order_amount > 0)
                                    <p class="text-sm text-gray-500 mt-3">
                                        Đơn tối thiểu:
                                        <strong>
                                            {{ number_format($voucher->min_order_amount, 0, ',', '.') }}đ
                                        </strong>
                                    </p>
                                @endif

                                <p class="text-xs text-gray-400 mt-2">
                                    @if($voucher->expires_at)
                                        Hạn sử dụng:
                                        {{ $voucher->expires_at->format('d/m/Y H:i') }}
                                    @else
                                        Không giới hạn thời gian
                                    @endif
                                </p>

                                <div class="mt-4 flex justify-end">

                                    <a
                                        href="{{ route('checkout.index') }}"
                                        class="bg-pink-500 hover:bg-pink-600 text-white px-5 py-2 rounded-lg text-sm font-semibold"
                                    >
                                        Dùng ngay
                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

    {{-- Voucher đã dùng --}}
    <div
        id="tab-used"
        class="voucher-tab-content hidden"
    >

        @if($usedVouchers->isEmpty())

            <div class="bg-white rounded-2xl border p-10 text-center text-gray-500">
                Chưa có voucher nào đã sử dụng.
            </div>

        @else

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                @foreach($usedVouchers as $voucher)

                    <div class="bg-white rounded-2xl border border-gray-200 p-5 opacity-75">

                        <div class="flex justify-between">

                            <div>

                                <h3 class="font-bold text-gray-700">
                                    {{ $voucher->name }}
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $voucher->code }}
                                </p>

                            </div>

                            <span class="bg-gray-100 text-gray-500 text-xs px-3 py-1 rounded-full h-fit">
                                Đã sử dụng
                            </span>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

    {{-- Voucher hết hạn --}}
    <div
        id="tab-expired"
        class="voucher-tab-content hidden"
    >

        @if($expiredVouchers->isEmpty())

            <div class="bg-white rounded-2xl border p-10 text-center text-gray-500">
                Không có voucher hết hạn.
            </div>

        @else

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                @foreach($expiredVouchers as $voucher)

                    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-5 opacity-60">

                        <div class="flex justify-between">

                            <div>

                                <h3 class="font-bold text-gray-600">
                                    {{ $voucher->name }}
                                </h3>

                                <p class="text-sm text-gray-400 mt-1">
                                    {{ $voucher->code }}
                                </p>

                                @if($voucher->expires_at)

                                    <p class="text-xs text-gray-400 mt-2">
                                        Đã hết hạn:
                                        {{ $voucher->expires_at->format('d/m/Y H:i') }}
                                    </p>

                                @endif

                            </div>

                            <span class="bg-red-50 text-red-500 text-xs px-3 py-1 rounded-full h-fit">
                                Hết hạn
                            </span>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const buttons = document.querySelectorAll('.voucher-tab');
    const contents = document.querySelectorAll('.voucher-tab-content');

    buttons.forEach(function (button) {

        button.addEventListener('click', function () {

            const tab = button.dataset.tab;

            buttons.forEach(function (item) {
                item.classList.remove(
                    'bg-pink-500',
                    'text-white'
                );

                item.classList.add(
                    'bg-gray-100',
                    'text-gray-600'
                );
            });

            button.classList.remove(
                'bg-gray-100',
                'text-gray-600'
            );

            button.classList.add(
                'bg-pink-500',
                'text-white'
            );

            contents.forEach(function (content) {
                content.classList.add('hidden');
            });

            document
                .getElementById('tab-' + tab)
                ?.classList
                .remove('hidden');

        });

    });

});
</script>
@endsection