@extends('admin.layouts.app')

@section('title', 'Vận chuyển (GHN)')

@section('content')
<div class="p-6 space-y-5">

    <h1 class="text-2xl font-bold">🚚 Vận chuyển (GHN)</h1>

    {{-- Thống kê nhanh --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4" id="stat-cards">
        @foreach([
            'pending'    => ['label' => 'Chờ lấy hàng', 'color' => 'yellow'],
            'picked'     => ['label' => 'Đã lấy hàng',  'color' => 'blue'],
            'delivering' => ['label' => 'Đang giao',     'color' => 'indigo'],
            'delivered'  => ['label' => 'Đã giao',       'color' => 'green'],
            'cancel'     => ['label' => 'Đã huỷ',        'color' => 'red'],
        ] as $status => $info)
        <div class="bg-white rounded-xl shadow p-4 text-center cursor-pointer hover:ring-2 hover:ring-indigo-300 stat-card"
             data-status="{{ $status }}"
             onclick="filterByStatus('{{ $status }}')">
            <p class="text-2xl font-bold text-{{ $info['color'] }}-600" id="count-{{ $status }}">
                {{ $counts[$status] ?? 0 }}
            </p>
            <p class="text-xs text-gray-500 mt-1">{{ $info['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Bộ lọc --}}
    <div class="flex gap-3 flex-wrap items-center">
        <div class="relative">
            <input type="text" id="search-input"
                placeholder="Tìm mã GHN, mã đơn..."
                class="border rounded-lg px-3 py-2 text-sm w-64 pr-8"
                oninput="debounceSearch()">
            <button onclick="document.getElementById('search-input').value='';fetchShipments()"
                    class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600 text-xs">✕</button>
        </div>

        <select id="status-select" class="border rounded-lg px-3 py-2 text-sm"
                onchange="fetchShipments()">
            <option value="">Tất cả trạng thái</option>
            <option value="pending">Chờ lấy hàng</option>
            <option value="picked">Đã lấy hàng</option>
            <option value="delivering">Đang giao</option>
            <option value="delivered">Đã giao</option>
            <option value="cancel">Đã huỷ</option>
        </select>
    </div>

    {{-- Bảng kết quả --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
                <tr>
                    <th class="text-left px-5 py-3">Mã GHN</th>
                    <th class="text-left px-5 py-3">Mã đơn</th>
                    <th class="text-left px-5 py-3">Người nhận</th>
                    <th class="text-left px-5 py-3">Phí ship</th>
                    <th class="text-left px-5 py-3">Trạng thái</th>
                    <th class="text-left px-5 py-3">Dự kiến giao</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody id="shipment-tbody">
                <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">Đang tải...</td></tr>
            </tbody>
        </table>
    </div>

    <div id="pagination" class="mt-2"></div>

</div>

@push('scripts')
<script>
let debounceTimer = null;

function debounceSearch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fetchShipments, 400);
}

function filterByStatus(status) {
    document.getElementById('status-select').value = status;
    fetchShipments();
}

function resetFilter() {
    document.getElementById('search-input').value = '';
    document.getElementById('status-select').value = '';
    fetchShipments();
}

async function fetchShipments(page = 1) {
    const q      = document.getElementById('search-input').value;
    const status = document.getElementById('status-select').value;
    const tbody  = document.getElementById('shipment-tbody');

    tbody.innerHTML = '<tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">Đang tải...</td></tr>';

    const params = new URLSearchParams({ q, status, page });
    const res = await fetch(`{{ route('admin.shipments.api') }}?${params}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });

    const data = await res.json();

    // Render rows
    if (data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">Chưa có vận đơn nào.</td></tr>';
        return;
    }

    const statusLabels = {
        pending: 'Chờ lấy hàng', picked: 'Đã lấy hàng',
        delivering: 'Đang giao', delivered: 'Đã giao', cancel: 'Đã huỷ'
    };
    const statusColors = {
        pending: 'yellow', picked: 'blue',
        delivering: 'indigo', delivered: 'green', cancel: 'red'
    };

    tbody.innerHTML = data.data.map(s => `
        <tr class="hover:bg-gray-50 border-b">
            <td class="px-5 py-3.5 font-mono font-semibold text-indigo-600">${s.ghn_order_code ?? '—'}</td>
            <td class="px-5 py-3.5">
                <a href="/admin/don-hang/${s.order_code}" class="text-blue-600 hover:underline">${s.order_code}</a>
            </td>
            <td class="px-5 py-3.5">
                <p class="font-medium">${s.recipient_name}</p>
                <p class="text-gray-400 text-xs">${s.recipient_phone}</p>
            </td>
            <td class="px-5 py-3.5">${Number(s.shipping_fee).toLocaleString('vi-VN')}đ</td>
            <td class="px-5 py-3.5">
                <span class="px-2 py-1 rounded-full text-xs font-medium bg-${statusColors[s.status] ?? 'gray'}-100 text-${statusColors[s.status] ?? 'gray'}-700">
                    ${statusLabels[s.status] ?? s.status}
                </span>
            </td>
            <td class="px-5 py-3.5 text-gray-500">${s.expected_delivery_at ?? '—'}</td>
            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                <a href="/admin/don-hang/${s.order_code}" class="text-indigo-600 hover:underline text-xs mr-3">Chi tiết</a>
                <a href="/admin/don-hang/${s.order_code}/in-van-don" target="_blank" class="text-blue-600 hover:underline text-xs">In vận đơn</a>
            </td>
        </tr>
    `).join('');

    // Cập nhật stat cards
    Object.entries(data.counts).forEach(([st, count]) => {
        const el = document.getElementById(`count-${st}`);
        if (el) el.textContent = count;
    });
}

// Load ngay khi vào trang
fetchShipments();
</script>
@endpush

@endsection