@extends('admin.layouts.app')

@section('title', 'Live Chat - ' . ($conversation->user?->name ?? 'Khách'))

@section('content')
<style>
    .live-chat-page {
        padding: 26px 30px;
    }

    .live-chat-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 18px;
    }

    .live-chat-back {
        color: #302a39;
        text-decoration: none;
        font-size: 14px;
        font-weight: 750;
    }

    .live-chat-back:hover {
        color: #ef526c;
    }

    .live-chat-session {
        color: #88818d;
        font-size: 13px;
    }

    .live-chat-shell {
        display: grid;
        grid-template-columns: 290px minmax(0, 1fr);
        min-height: 660px;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e9e4ec;
        border-radius: 18px;
    }

    .live-chat-side {
        padding: 22px;
        border-right: 1px solid #eee9f0;
        background: #fcfbfd;
    }

    .live-chat-avatar {
        width: 58px;
        height: 58px;
        margin-bottom: 13px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: #ffe5ea;
        color: #6b4a9f;
        font-size: 23px;
        font-weight: 800;
    }

    .live-chat-side h2 {
        margin: 0;
        color: #211c28;
        font-size: 20px;
        font-weight: 800;
    }

    .live-chat-muted {
        color: #85808a;
        font-size: 13px;
    }

    .live-chat-customer-meta {
        margin-top: 5px;
    }

    .live-chat-info {
        display: grid;
        gap: 11px;
        margin-top: 20px;
    }

    .live-chat-info-item {
        padding: 12px 13px;
        border: 1px solid #ece7ef;
        border-radius: 12px;
        background: #fff;
    }

    .live-chat-info-item small {
        display: block;
        margin-bottom: 3px;
        color: #96909b;
        font-size: 12px;
    }

    .live-chat-info-item strong {
        color: #28222f;
        font-size: 14px;
    }

    .live-chat-actions {
        margin-top: 18px;
    }

    .live-chat-actions form {
        margin-bottom: 9px;
    }

    .live-chat-btn {
        width: 100%;
        padding: 11px 14px;
        cursor: pointer;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 800;
    }

    .live-chat-btn.accept {
        border: 0;
        background: #ff6179;
        color: #fff;
    }

    .live-chat-btn.close {
        border: 1px solid #ffc0cb;
        background: #fff1f3;
        color: #c83c55;
    }

    .live-chat-main {
        display: grid;
        grid-template-rows: auto minmax(0, 1fr) auto;
        min-width: 0;
    }

    .live-chat-head {
        padding: 17px 20px;
        border-bottom: 1px solid #eee9f0;
    }

    .live-chat-head strong {
        display: block;
        color: #231d2a;
        font-size: 17px;
    }

    .live-chat-messages {
        height: 520px;
        padding: 20px;
        overflow-y: auto;
        background: #fffafb;
    }

    .live-chat-row {
        display: flex;
        margin-bottom: 13px;
    }

    .live-chat-row.customer {
        justify-content: flex-start;
    }

    .live-chat-row.staff,
    .live-chat-row.bot {
        justify-content: flex-end;
    }

    .live-chat-row.system {
        justify-content: center;
    }

    .live-chat-bubble {
        max-width: 72%;
        padding: 11px 13px;
        border: 1px solid #e9e5e9;
        border-radius: 15px;
        background: #fff;
        color: #26212b;
        line-height: 1.5;
    }

    .live-chat-row.staff .live-chat-bubble {
        border-color: #ff6179;
        background: #ff6179;
        color: #fff;
    }

    .live-chat-row.bot .live-chat-bubble {
        border-color: #e3ddff;
        background: #f0ecff;
    }

    .live-chat-row.system .live-chat-bubble {
        border: 0;
        background: transparent;
        color: #8c858e;
        font-size: 12px;
        text-align: center;
    }

    .live-chat-sender {
        display: block;
        margin-bottom: 4px;
        font-size: 11px;
        font-weight: 800;
        opacity: .75;
    }

    .live-chat-time {
        display: block;
        margin-top: 5px;
        text-align: right;
        font-size: 10px;
        opacity: .6;
    }

    .live-chat-composer {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        padding: 14px;
        border-top: 1px solid #eee9f0;
        background: #fff;
    }

    .live-chat-composer textarea {
        min-height: 48px;
        resize: none;
        outline: none;
        border: 1px solid #ded9e2;
        border-radius: 13px;
        padding: 11px 13px;
        font: inherit;
    }

    .live-chat-composer textarea:focus {
        border-color: #ff8093;
    }

    .live-chat-composer button {
        min-width: 82px;
        cursor: pointer;
        border: 0;
        border-radius: 13px;
        background: #ff6179;
        color: #fff;
        font-size: 14px;
        font-weight: 800;
    }

    .live-chat-notice {
        padding: 16px;
        border-top: 1px solid #eee9f0;
        background: #faf9fb;
        color: #79737e;
        text-align: center;
        font-size: 13px;
    }

    @media (max-width: 900px) {
        .live-chat-page {
            padding: 20px;
        }

        .live-chat-shell {
            grid-template-columns: 1fr;
        }

        .live-chat-side {
            border-right: 0;
            border-bottom: 1px solid #eee9f0;
        }

        .live-chat-messages {
            height: 55vh;
        }
    }
</style>

<div class="live-chat-page">

    <div class="live-chat-top">
        <a
            href="{{ route('admin.chats.index') }}"
            class="live-chat-back"
        >
            ← Danh sách hội thoại
        </a>

        <span class="live-chat-session">
            Phiên #{{ $conversation->id }}
        </span>
    </div>

    <div class="live-chat-shell">

        <aside class="live-chat-side">

            <div class="live-chat-avatar">
                KH
            </div>

            <h2>
                {{ $conversation->user?->name ?? 'Khách vãng lai' }}
            </h2>

            <div class="live-chat-muted live-chat-customer-meta">
                {{ $conversation->user?->email ?? 'Phiên khách chưa đăng nhập' }}
            </div>

            <div class="live-chat-info">

                <div class="live-chat-info-item">
                    <small>Trạng thái</small>

                    <strong id="side-status">
                        {{ $conversation->status }}
                    </strong>
                </div>

                <div class="live-chat-info-item">
                    <small>Bắt đầu</small>

                    <strong>
                        {{ $conversation->started_at?->format('H:i d/m/Y') }}
                    </strong>
                </div>

                <div class="live-chat-info-item">
                    <small>Nhân viên phụ trách</small>

                    <strong id="side-staff">
                        {{ $conversation->staff?->name ?? 'Chưa có' }}
                    </strong>
                </div>

            </div>

            <div class="live-chat-actions">

                @if ($conversation->status === 'waiting_staff')
                    <form
                        method="POST"
                        action="{{ route('admin.chats.accept', $conversation) }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="live-chat-btn accept"
                        >
                            Tiếp nhận cuộc trò chuyện
                        </button>
                    </form>
                @endif

                @if ($conversation->status === 'staff_connected')
                    <form
                        method="POST"
                        action="{{ route('admin.chats.close', $conversation) }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="live-chat-btn close"
                        >
                            Đóng phiên
                        </button>
                    </form>
                @endif

            </div>

        </aside>

        <main class="live-chat-main">

            <div class="live-chat-head">
                <strong>Live Chat</strong>

                <div
                    class="live-chat-muted"
                    id="chat-state-label"
                >
                    {{ $conversation->status }}
                </div>
            </div>

            <div
                class="live-chat-messages"
                id="admin-chat-messages"
            >
                @foreach ($messages as $message)

                    <div class="live-chat-row {{ $message->sender_type }}">

                        <div class="live-chat-bubble">

                            <span class="live-chat-sender">

                                @if ($message->sender_type === 'customer')
                                    {{ $conversation->user?->name ?? 'Khách' }}

                                @elseif ($message->sender_type === 'staff')
                                    {{ $message->admin?->name ?? 'Nhân viên' }}

                                @elseif ($message->sender_type === 'bot')
                                    MommyKids Bot

                                @else
                                    Hệ thống
                                @endif

                            </span>

                            <div>
                                {{ $message->message }}
                            </div>

                            <span class="live-chat-time">
                                {{ $message->created_at?->format('H:i') }}
                            </span>

                        </div>

                    </div>

                @endforeach
            </div>

            @if (
                $conversation->status === 'staff_connected'
                &&
                (string) $conversation->staff_id
                    === (string) auth('admin')->id()
            )

                <form
                    class="live-chat-composer"
                    id="admin-chat-form"
                >
                    <textarea
                        id="admin-chat-input"
                        rows="2"
                        maxlength="2000"
                        placeholder="Nhập nội dung tư vấn..."
                    ></textarea>

                    <button type="submit">
                        Gửi
                    </button>
                </form>

            @else

                <div class="live-chat-notice">

                    @if ($conversation->status === 'waiting_staff')
                        Hãy tiếp nhận phiên để bắt đầu tư vấn trực tiếp.

                    @elseif ($conversation->status === 'bot')
                        Phiên này hiện vẫn đang được Bot xử lý.

                    @elseif ($conversation->status === 'closed')
                        Phiên trò chuyện đã kết thúc.

                    @else
                        Cuộc trò chuyện đang do nhân viên khác phụ trách.
                    @endif

                </div>

            @endif

        </main>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const messagesUrl = @json(
        route(
            'admin.chats.messages',
            $conversation,
            false
        )
    );

    const sendUrl = @json(
        route(
            'admin.chats.messages.send',
            $conversation,
            false
        )
    );

    const csrf = @json(csrf_token());

    const box =
        document.getElementById('admin-chat-messages');

    const form =
        document.getElementById('admin-chat-form');

    const input =
        document.getElementById('admin-chat-input');

    const sideStatus =
        document.getElementById('side-status');

    const sideStaff =
        document.getElementById('side-staff');

    const stateLabel =
        document.getElementById('chat-state-label');

    let lastKey = '';

    const escapeHtml = value => {
        const div = document.createElement('div');

        div.textContent = String(value ?? '');

        return div.innerHTML;
    };

    async function request(url, options = {}) {

        const response = await fetch(url, {
            ...options,

            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                ...(options.headers || {}),
            },
        });

        const data =
            await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(
                data.message || 'Có lỗi xảy ra.'
            );
        }

        return data;
    }

    function render(data) {

        const key = JSON.stringify(
            data.messages.map(message => message.id)
        );

        if (key !== lastKey) {

            box.innerHTML = data.messages
                .map(message => `
                    <div class="live-chat-row ${escapeHtml(message.sender_type)}">

                        <div class="live-chat-bubble">

                            <span class="live-chat-sender">
                                ${escapeHtml(message.sender_name)}
                            </span>

                            <div>
                                ${escapeHtml(message.message)
                                    .replace(/\n/g, '<br>')}
                            </div>

                            <span class="live-chat-time">
                                ${escapeHtml(message.time || '')}
                            </span>

                        </div>

                    </div>
                `)
                .join('');

            box.scrollTop = box.scrollHeight;

            lastKey = key;
        }

        sideStatus.textContent =
            data.conversation.status;

        sideStaff.textContent =
            data.conversation.staff_name
            || 'Chưa có';

        const statusLabels = {
            bot: 'Bot đang hỗ trợ khách',
            waiting_staff:
                'Khách đang chờ nhân viên tiếp nhận',
            staff_connected:
                'Đang tư vấn trực tiếp',
            closed:
                'Phiên đã kết thúc',
        };

        stateLabel.textContent =
            statusLabels[data.conversation.status]
            || data.conversation.status;

        if (
            data.conversation.status === 'closed'
            && form
        ) {
            form.querySelector('textarea').disabled = true;
            form.querySelector('button').disabled = true;
        }
    }

    async function poll() {

        try {
            render(
                await request(messagesUrl)
            );
        } catch (error) {
            console.error(error);
        }
    }

    form?.addEventListener(
        'submit',
        async event => {

            event.preventDefault();

            const message =
                input.value.trim();

            if (!message) {
                return;
            }

            const button =
                form.querySelector('button');

            input.disabled = true;
            button.disabled = true;

            try {

                const data = await request(
                    sendUrl,
                    {
                        method: 'POST',

                        body: JSON.stringify({
                            message,
                        }),
                    }
                );

                input.value = '';

                render(data);

            } catch (error) {

                alert(error.message);

            } finally {

                input.disabled = false;
                button.disabled = false;

                input.focus();
            }
        }
    );

    input?.addEventListener(
        'keydown',
        event => {

            if (
                event.key === 'Enter'
                && !event.shiftKey
            ) {
                event.preventDefault();

                form.requestSubmit();
            }
        }
    );

    box.scrollTop =
        box.scrollHeight;

    poll();

    setInterval(
        poll,
        2000
    );
});
</script>
@endsection