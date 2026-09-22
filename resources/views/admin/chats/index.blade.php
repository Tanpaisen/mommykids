@extends('admin.layouts.app')

@section('title', 'Live Chat')

@section('content')
<style>
    .chat-admin-page {
        padding: 28px 30px;
    }

    .chat-page-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .chat-page-head h1 {
        margin: 0;
        font-size: 26px;
        font-weight: 800;
        color: #201c2b;
    }

    .chat-page-head p {
        margin: 6px 0 0;
        color: #777180;
        font-size: 14px;
    }

    .chat-back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border-radius: 12px;
        border: 1px solid #e7e2eb;
        background: #fff;
        color: #332d3d;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
    }

    .chat-back-btn:hover {
        border-color: #ff7288;
        color: #ef526c;
    }

    .chat-flash {
        padding: 13px 16px;
        margin-bottom: 18px;
        border-radius: 12px;
        background: #ecfdf3;
        color: #166534;
        font-size: 14px;
        font-weight: 600;
    }

    .chat-tabs {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .chat-tab {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 72px;
        padding: 16px 18px;
        border: 1px solid #e8e4ec;
        border-radius: 16px;
        background: #fff;
        color: #302a39;
        font-size: 15px;
        font-weight: 750;
        text-decoration: none;
        transition: .2s ease;
    }

    .chat-tab:hover {
        border-color: #ffc1cb;
        transform: translateY(-1px);
    }

    .chat-tab.active {
        border-color: #ff6b82;
        background: #fff2f5;
        color: #e94762;
    }

    .chat-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 8px;
        border-radius: 999px;
        background: #f2f2f5;
        color: #3e3945;
        font-size: 13px;
        font-weight: 800;
    }

    .chat-tab.active .chat-count {
        background: #ff637c;
        color: #fff;
    }

    .chat-list {
        display: grid;
        gap: 12px;
    }

    .chat-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 20px;
        padding: 18px 20px;
        border: 1px solid #ebe7ee;
        border-radius: 16px;
        background: #fff;
        color: inherit;
        text-decoration: none;
        transition: .2s ease;
    }

    .chat-card:hover {
        border-color: #ffb9c5;
        box-shadow: 0 8px 24px rgba(33, 24, 45, .06);
        transform: translateY(-1px);
    }

    .chat-customer {
        font-size: 16px;
        font-weight: 800;
        color: #251f2c;
        margin-bottom: 7px;
    }

    .chat-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px 16px;
        color: #777180;
        font-size: 13px;
    }

    .chat-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 11px;
        border-radius: 999px;
        white-space: nowrap;
        font-size: 12px;
        font-weight: 800;
    }

    .chat-badge.waiting_staff {
        background: #fff4d9;
        color: #a26500;
    }

    .chat-badge.staff_connected {
        background: #e9f3ff;
        color: #276da8;
    }

    .chat-badge.bot {
        background: #f0ebff;
        color: #7250b5;
    }

    .chat-badge.closed {
        background: #f1f2f4;
        color: #686d76;
    }

    .chat-empty {
        padding: 60px 24px;
        text-align: center;
        border: 1px dashed #dad5df;
        border-radius: 16px;
        background: #fff;
        color: #817a88;
        font-size: 14px;
    }

    .chat-pagination {
        margin-top: 20px;
    }

    @media (max-width: 900px) {
        .chat-admin-page {
            padding: 20px;
        }

        .chat-tabs {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .chat-page-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .chat-tabs {
            grid-template-columns: 1fr;
        }

        .chat-card {
            grid-template-columns: 1fr;
        }

        .chat-badge {
            justify-self: start;
        }
    }
</style>

<div class="chat-admin-page">

    <div class="chat-page-head">
        <div>
            <h1>Live Chat MommyKids</h1>
            <p>Tiếp nhận và tư vấn trực tiếp cho khách hàng.</p>
        </div>

        <a
            href="{{ route('admin.customer-care.index', ['tab' => 'live']) }}"
            class="chat-back-btn"
        >
            ← Chăm sóc khách hàng
        </a>
    </div>

    @if (session('success'))
        <div class="chat-flash">
            {{ session('success') }}
        </div>
    @endif

    <nav class="chat-tabs">

        <a
            href="{{ route('admin.chats.index', ['tab' => 'waiting']) }}"
            class="chat-tab {{ $tab === 'waiting' ? 'active' : '' }}"
        >
            <span>Chờ tiếp nhận</span>
            <span class="chat-count">
                {{ $counts['waiting'] }}
            </span>
        </a>

        <a
            href="{{ route('admin.chats.index', ['tab' => 'mine']) }}"
            class="chat-tab {{ $tab === 'mine' ? 'active' : '' }}"
        >
            <span>Phiên của tôi</span>
            <span class="chat-count">
                {{ $counts['mine'] }}
            </span>
        </a>

        <a
            href="{{ route('admin.chats.index', ['tab' => 'bot']) }}"
            class="chat-tab {{ $tab === 'bot' ? 'active' : '' }}"
        >
            <span>Bot đang hỗ trợ</span>
            <span class="chat-count">
                {{ $counts['bot'] }}
            </span>
        </a>

        <a
            href="{{ route('admin.chats.index', ['tab' => 'all']) }}"
            class="chat-tab {{ $tab === 'all' ? 'active' : '' }}"
        >
            <span>Tất cả hội thoại</span>
            <span class="chat-count">
                {{ $counts['all'] }}
            </span>
        </a>

    </nav>

    <div class="chat-list">

        @forelse ($conversations as $conversation)

            @php
                $labels = [
                    'bot' => 'Bot đang hỗ trợ',
                    'waiting_staff' => 'Chờ tiếp nhận',
                    'staff_connected' => 'Đang tư vấn',
                    'closed' => 'Đã kết thúc',
                ];
            @endphp

            <a
                href="{{ route('admin.chats.show', $conversation) }}"
                class="chat-card"
            >
                <div>

                    <div class="chat-customer">
                        {{ $conversation->user?->name ?? 'Khách vãng lai' }}
                    </div>

                    <div class="chat-meta">

                        @if ($conversation->user?->email)
                            <span>
                                {{ $conversation->user->email }}
                            </span>
                        @endif

                        <span>
                            {{ $conversation->messages_count }} tin nhắn
                        </span>

                        @if ($conversation->staff)
                            <span>
                                Nhân viên:
                                {{ $conversation->staff->name }}
                            </span>
                        @endif

                        <span>
                            {{ $conversation->last_message_at?->format('H:i d/m/Y') }}
                        </span>

                    </div>

                </div>

                <span class="chat-badge {{ $conversation->status }}">
                    {{ $labels[$conversation->status] ?? $conversation->status }}
                </span>

            </a>

        @empty

            <div class="chat-empty">
                Không có cuộc trò chuyện trong nhóm này.
            </div>

        @endforelse

    </div>

    @if ($conversations->hasPages())
        <div class="chat-pagination">
            {{ $conversations->links() }}
        </div>
    @endif

</div>
@endsection